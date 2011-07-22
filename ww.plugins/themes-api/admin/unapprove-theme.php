<?php
/**
	* accessed via AJAX, this file unapproves a theme for
	* addition to the repository and sends an email to the
	* author telling them that
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$theme_id = addslashes(@$_POST[ 'theme_id']);
$user_id = addslashes(@$_POST[ 'user_id']);

if ($theme_id == 0 || $user_id == 0) {
	die('error');
}

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.incs/mail.php';

/**
 * get data on the theme and the user
 */
$user = dbRow('select name,email from user_accounts where id=' . $user_id);
$theme_name=dbOne('select name from themes_api where id='.$theme_id, 'name');

/**
 * delete theme from themes-personal
 */
shell_exec('rm -rf ' . USERBASE . 'themes-personal/' . $theme_name);

/**
 * mark the theme as unmoderated in the db
 */
dbQuery('update themes_api set moderated="no" where id=' . $theme_id);

/**
 * send the user an email telling them the theme
 * was marked for approval again
 */
$body='<h3>Theme Removed</h3> <p>Hi '.$user['name'].',</p><p>Your theme nam'
	.'ed "' . $theme_name . '" has been marked for approval by moderaters for'
	.' re-addition to the theme repository.</p> <p>Thanks<br/>---<br/> KvWebm'
	.'e</p>';

send_mail(
	$user[ 'email' ],
	'noreply@' . $_SERVER[ 'HTTP_HOST' ],
	'Theme Removed',
	$body,
	false
);

die( 'ok' );
