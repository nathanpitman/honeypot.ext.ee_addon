<?php

/*
=============================================================
 	Developed by Aidann Bowley, bridgingunit.com
 	Hacked by Nathan Pitman, ninefour.co.uk
=============================================================
	File:					ext.nf_user_freeform_honeypot.php
-------------------------------------------------------------
	Compatibility (tested):	ExpressionEngine 1.7.1 
-------------------------------------------------------------
	Purpose:				Help limit User module and Freeform module spam by testing against a field that should not be filled in
-------------------------------------------------------------
	Inspiration:			
=============================================================
	Version History:
	
	0.9.0 - 2 February 2010 - extension first built	
	0.9.1 - 17 October 2012 - extension hacked to work with 
=============================================================


*/

if ( ! defined('EXT')) exit('Invalid file request');

class Nf_user_freeform_honeypot
{
    var $settings        = array();

	var $name            = 'NF User Freeform Honeypot';
    var $version         = '0.9.1';
    var $description     = 'Help limit User module and Freeform module spam by testing against a field that should not be completed, a honeypot.';
    var $settings_exist  = 'y';
    var $docs_url        = '';//'http://expressionengine.com';
       
    // -------------------------------
    //   Constructor - Extensions use this for settings
    // -------------------------------    
    function Nf_user_freeform_honeypot($settings='')
    {
        $this->settings = $settings;
    }
    // END

    // --------------------------------
	//  Activate Extension
	// --------------------------------	
	function activate_extension()
	{
	    global $DB;
    									    
	    $DB->query($DB->insert_string('exp_extensions',
				array(
				'extension_id' => '',
				'class'        => "Nf_user_freeform_honeypot",
				'method'       => "honeypot",
				'hook'         => "freeform_module_validate_end",
				'settings'     => "",
				'priority'     => 1,
				'version'      => $this->version,
				'enabled'      => "y"
				)
			)
		);
		
		$DB->query($DB->insert_string('exp_extensions',
				array(
				'extension_id' => '',
				'class'        => "Nf_user_freeform_honeypot",
				'method'       => "honeypot",
				'hook'         => "user_register_end",
				'settings'     => "",
				'priority'     => 1,
				'version'      => $this->version,
				'enabled'      => "y"
				)
			)
		);
	}
	// END  
	
	// --------------------------------
	//  Update Extension
	// --------------------------------  
	function update_extension($current = '')
	{
		global $DB;
		
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		$DB->query("UPDATE exp_extensions 
					SET version = '".$DB->escape_str($this->version)."' 
					WHERE class = 'Nf_user_freeform_honeypot'");
	}
	// END

	// --------------------------------
	//  Disable Extension
	// --------------------------------	
	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'Nf_user_freeform_honeypot'");
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
    	global $EXT, $OUT, $IN, $FNS, $LANG;

		// Check that we're not the first one using this hook
		// and, if not, use the returned value from the last	
		if ($EXT->last_call !== FALSE)
		{
			$errors = $EXT->last_call;
		}
				
		$LANG->fetch_language_file('nf_user_freeform_honeypot');	
	
		// For greater ease of use
		$honeypot_field = $this->settings['honeypot_field_name'];
		
		/* Testing
		
		// if no setting, 
		// if no field of the setting name, 
		// if field of the setting name is not a textfield
		// proceed without validation
		
		if($honeypot_field === '')
		{
			$EXT->end_script = TRUE;
			return $OUT->show_user_error('general', 'no setting');
		}
		if(! isset($_POST[$honeypot_field]))
		{
			$EXT->end_script = TRUE;
			return $OUT->show_user_error('general', 'no field of the setting name');
		}		
		if(is_array($_POST[$honeypot_field]))
		{
			$EXT->end_script = TRUE;
			return $OUT->show_user_error('general', 'field of the setting name is not a textfield');
		}
		*/
		
		if($honeypot_field !== '' AND isset($_POST[$honeypot_field]) AND ! is_array($_POST[$honeypot_field]))
		{
			// Validate honeypot field
			// we want no flies in our honey! (to mix metaphors)
			if( $_POST[$honeypot_field] !== '' )
			{
				//	It ends here
				$EXT->end_script = TRUE;
				
				// 	If we want to show an error, show it, else fail as silently as we can
				if( $this->settings['show_error'] === 'yes' )
				{
					return $OUT->show_user_error('general', $LANG->line('err_tripped_trap'));	
				}
				else
				{	
					// redirect to site index
					// could redirect to return param: $IN->GBL('return') etc, 
					// but would need to do all checks line 1511+ in mod.freeform.php
					// and that has some funny chars decoding substitution going on for the entry_id
					$FNS->redirect( $FNS->fetch_site_index() );
				}			
			}	
		}

		// We're clean, give back what we have received
		return $errors;		
	}
	// END
}
// END class      
?>