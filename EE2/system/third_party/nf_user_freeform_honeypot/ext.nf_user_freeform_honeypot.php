<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
=============================================================
 	Developed by Aidann Bowley, bridgingunit.com
 	Hacked by Nathan Pitman, ninefour.co.uk
=============================================================
	File:					ext.nf_user_freeform_honeypot.php
-------------------------------------------------------------
	Compatibility (tested):	ExpressionEngine 2.4.0 
-------------------------------------------------------------
	Purpose:				Help limit User module and Freeform module spam by testing against a field that should not be filled in
-------------------------------------------------------------
	Inspiration:			
=============================================================
	Version History:
	
	0.9.0 - 2 February 2010 - extension first built	
	0.9.1 - 17 October 2012 - extension hacked to work with freeform
	0.9.2 - 20 March 2013 - migrated to EE2.x
=============================================================


*/

class Nf_user_freeform_honeypot_ext
{
    var $settings        = array();

	var $name            = 'NF User Freeform Honeypot';
    var $version         = '0.9.2';
    var $description     = 'Help limit User module and Freeform module spam by testing against a field that should not be completed, a honeypot.';
    var $settings_exist  = 'y';
    var $docs_url        = 'https://github.com/ninefour/user_freeform_honeypot.ext.ee_addon';//'http://expressionengine.com';
       
    // -------------------------------
    //   Constructor - Extensions use this for settings
    // -------------------------------    
    function Nf_user_freeform_honeypot_ext($settings='')
    {
        $this->EE =& get_instance();
        
        $this->settings = $settings;
    }
    // END

    // --------------------------------
	//  Activate Extension
	// --------------------------------	
	function activate_extension()
	{
		
		$settings = $this->settings();
		
		$hooks = array(
			'freeform_module_validate_end' => 'honeypot',
			'user_register_end' => 'honeypot'
			);
		
		$this->_add_hooks($hooks, $settings);

	}
	// END 
	
	function _add_hooks($hooks=array(), $settings=array())
	{
		
		foreach ($hooks as $hook => $method)
		{
			
			// data to insert
			$data = array(
				'class'		=> get_class($this),
				'method'	=> $method,
				'hook'		=> $hook,
				'priority'	=> 1,
				'version'	=> $this->version,
				'enabled'	=> 'y',
				'settings'	=> serialize($settings)
			);
			
			// insert in database
			$this->EE->db->insert('extensions', $data);
		
		}
		
	} 

	// --------------------------------
	//  Disable Extension
	// --------------------------------	
	function disable_extension()
	{	    
	    $this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
	// END	

	// --------------------------------
	//  Settings
	// --------------------------------  	
	function settings()
	{
	    $settings = array();	    
	    
	    $settings['honeypot_field_name']   = 'swine';
	    //$settings['show_error']    = 'y';
	    $settings['show_error']   = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
	    
	    // Complex:
	    // [variable_name] => array(type, values, default value)
	    // variable_name => short name for setting and used as the key for language file variable
	    // type:  t - textarea, r - radio buttons, s - select, ms - multiselect, f - function calls
	    // values:  can be array (r, s, ms), string (t), function name (f)
	    // default:  name of array member, string, nothing
	    //
	    // Simple:
	    // [variable_name] => 'Butter'
	    // Text input, with 'Butter' as the default.
	    
	    return $settings;
	}
	// END

	// --------------------------------
	//  The honey, so to speak
	// --------------------------------		
	function honeypot($errors = array())
	{

		$this->EE->lang->loadfile('nf_user_freeform_honeypot');

		// Check that we're not the first one using this hook
		// and, if not, use the returned value from the last	
		if ($this->EE->extensions->last_call !== FALSE)
		{
			$errors = $this->EE->extensions->last_call;
		}
	
		// For greater ease of use
		$honeypot_field = $this->settings['honeypot_field_name'];
		
		/* Testing
		
		// if no setting, 
		// if no field of the setting name, 
		// if field of the setting name is not a textfield
		// proceed without validation
		
		if($honeypot_field === '')
		{
			$this->EE->extensions->end_script = TRUE;
			return $this->EE->output->show_user_error('general', 'no setting');
		}
		if(! isset($_POST[$honeypot_field]))
		{
			$this->EE->extensions->end_script = TRUE;
			return $this->EE->output->show_user_error('general', 'no field of the setting name');
		}		
		if(is_array($_POST[$honeypot_field]))
		{
			$this->EE->extensions->end_script = TRUE;
			return $this->EE->output->show_user_error('general', 'field of the setting name is not a textfield');
		}
		*/
		
		if($honeypot_field !== '' AND isset($_POST[$honeypot_field]) AND ! is_array($_POST[$honeypot_field]))
		{
			// Validate honeypot field
			// we want no flies in our honey! (to mix metaphors)
			if( $_POST[$honeypot_field] !== '' )
			{
				//	It ends here
				$this->EE->extensions->end_script = TRUE;
				
				// 	If we want to show an error, show it, else fail as silently as we can
				if( $this->settings['show_error'] === 'yes' )
				{
					return $this->EE->output->show_user_error('general', lang('err_tripped_trap'));	
				}
				else
				{	
					// redirect to site index
					// could redirect to return param: $IN->GBL('return') etc, 
					// but would need to do all checks line 1511+ in mod.freeform.php
					// and that has some funny chars decoding substitution going on for the entry_id
					$this->EE->functions->redirect( $this->EE->functions->fetch_site_index() );
				}			
			}	
		}

		// We're clean, give back what we have received
		return $errors;		
	}
	// END
}


/* End of file ext.nf_user_freeform_honeypot.php */
/* Location: ./system/expressionengine/third_party/ext.nf_user_freeform_honeypot.php */