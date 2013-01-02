<?php
/**
	* sms admin setup
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!Core_isAdmin()) {
	Core_quit();
}

if (isset($_REQUEST['account']) && $_REQUEST['account']=='new') {
	echo '<table id="sms_account_setup">'
		.'<tr><th>Admin\'s email address</th><td><input id="sms_email" /></td></tr>'
		.'<tr><th>Preferred Password</th><td><input id="sms_password" /></td></tr>'
		.'<tr><th>repeat Password</th><td><input id="sms_password2" /></td></tr>'
		.'<tr><th>Phone Number</th><td>'
		.'<input id="sms_phone_number" /></td></tr>'
		.'<tr><td colspan="2" id="sms_messages">Please fill in the above form.</td></tr>'
		.'</table><script src="/ww.plugins/sms/admin/setup.js"></script>';
}
