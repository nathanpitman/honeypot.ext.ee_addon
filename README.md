User/Freeform Honeypot for ExpressionEngine
===========================================
Originally developed by <a href="http://bridgingunit.com">Aidann Bowley</a>, later hacked by Nathan Pitman of <a href="http://github.com/ninefour">Nine Four</a> to work with Freeform and then migrated to EE2.x.

The "User Freeform Honeypot" extension helps to limit Solspace User module and Solspace Freeform module spam by testing against a field that should not be completed, a honeypot.


<b>Usage example:</b>

Enable the extension and specify the name of the field in your form which you will use as the honeypot through the extension settings screen. This defaults to 'swine' but we suggest you change it to something which is less likely to reveal it's intent.

Manually add the corresponding field to your front end 'User' and/or 'Freeform' forms:

```c
<input type="text" name="swine">
```
Hide the field in question using CSS:

```c
input[name=swine] {
	display: none;
}
```

(Most) humans will never see the field, as such they will never complete it, the form will only accept a submission if the honeypot is empty. Most robots will add content to the honeypot and fail the form submission.

You can also optionally  have an error message shown if the spammer fills in the honeypot field. If left to default to 'no' then User and Freeform forms will simply redirect to the site index when they catch somethign in the honeypot.
