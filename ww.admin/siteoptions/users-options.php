<?php
/**
	* User management - options
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (@$_REQUEST['action']=='save') {
	Core_siteVar('useraccounts_registrationtokenemail_from', $_REQUEST['email']);
	Core_siteVar(
		'useraccounts_registrationtokenemail_subject',
		$_REQUEST['subject']
	);
	Core_siteVar(
		'useraccounts_registrationtokenemail_message',
		$_REQUEST['message']
	);
}

echo '<h3>User Options</h3>'
	.'<p>This list of options governs how users are created.</p>';
$rs=dbAll('select * from site_vars where name like "useraccounts_%"', 'name');
echo '<form action="./siteoptions.php?page=users&amp;tab=options" '
	.'method="post">';
echo '<div id="user-options-wrapper">';

// { token email
echo '<h2><a href="#">User Token email</a></h2><div><table>'
	.'<tr><th>From email address</th><td><input name="email" value="'
	.htmlspecialchars(Core_siteVar('useraccounts_registrationtokenemail_from'))
	.'"/></td></tr>'
	.'<tr><th>Subject</th><td><input name="subject" value="'
	.htmlspecialchars(Core_siteVar('useraccounts_registrationtokenemail_subject'))
	.'"/></td></tr>'
	.'<tr><th>Message</th><td><textarea name="message">'
	.htmlspecialchars(Core_siteVar('useraccounts_registrationtokenemail_message'))
	.'</textarea></td><td><strong>codes</strong><br/>'
	.'<code>%token%</code>: the registration token</td></tr>'
	.'</table></div>';
// }

echo '</div><input type="hidden" name="action" value="save"/>'
	.'<input type="submit" value="Save"/></form>';
WW_addScript('/ww.admin/siteoptions/users-options.js');
WW_addCSS('/ww.admin/siteoptions/users-options.css');
