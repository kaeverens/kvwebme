<?php
/**
	* accessed via AJAX, this file deletes a theme from
	* the repository and informs the author
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

$theme_id = addslashes(@$_POST[ 'theme_id' ]);
$user_id = addslashes(@$_POST[ 'user_id' ]);

if ($theme_id == 0 || $user_id == 0) {
	die( 'error' );
}

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.incs/mail.php';

/**
 * get data on the theme and the user
 */
$user = dbRow('select name,email from user_accounts where id=' . $user_id);
$theme_name=dbOne('select name from themes_api where id='.$theme_id, 'name');

/**
 * delete theme from user-files
 * and from themes-personal
 */
shell_exec('rm -rf ' . USERBASE.'/f/themes_api/themes/' . $theme_id);
shell_exec('rm -rf ' . USERBASE.'/themes-personal/' . $theme_name);

/**
 * delete the theme from the db
 */
dbQuery('delete from  themes_api where id=' . $theme_id);

/**
 * send the user an email telling them the theme
 * was deleted
 */
$cms_name=DistConfig::get('cms-name');
$body='<h3>'.__('Theme Deleted').'</h3><p>'
	.__('Hi %1,', array($user['name']), 'core').'</p><p>'
	.__(
		'Your theme named "%1" has been deleted by moderaters.',
		array($theme_name), 'core'
	)
	.'</p><p>'.__('Contact us if you have questions.').'</p><p>'
	.__('Thanks<br/>---<br/>%1', array($cms_name), 'core')
	.'</p>';
send_mail(
	$user[ 'email' ],
	'no-reply@' . $_SERVER[ 'HTTP_HOST' ],
	__('Theme Deleted'),
	$body,
	false
);

die( 'ok' );
