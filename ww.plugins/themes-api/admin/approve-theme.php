<?php
/**
	* accessed via AJAX, this file approves a theme for
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

$theme_id = addslashes(@$_POST[ 'theme_id' ]);
$user_id = addslashes(@$_POST[ 'user_id' ]);

if ($theme_id == 0 || $user_id == 0) {
	die('error');
}

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.incs/mail.php';

/**
 * get data on the theme and the user
 */
$user=dbRow('select name,email from user_accounts where id=' . $user_id);
$theme_name=dbOne('select name from themes_api where id='.$theme_id, 'name');

/**
 * mark the theme as moderated in the db
 */
dbQuery('update themes_api set moderated="yes" where id=' . $theme_id);

/**
 * install theme on server
 */
shell_exec(
	'cp -R ' . USERBASE.'/f/themes_api/themes/' . $theme_id . '/' 
	. $theme_id . '.zip ' . USERBASE.'/themes-personal'
);
shell_exec('cd '.USERBASE.'/themes-personal && unzip -o '.$theme_id.'.zip');
shell_exec('rm -rf ' . USERBASE.'/themes-personal/' . $theme_id . '.zip');

/**
 * send the user an email telling them the theme
 * was approved
 */
$cms_name=DistConfig::get('cms-name');
$body='<h3>'.__('Theme Approved').'</h3><p>'
	.__('Hi %1,', array($user['name']), 'core').'</p>'
	.'<p>'.__(
		'Your theme named "%1" has been approved by moderaters '
		.'for addition to the theme repository.',
		array($theme_name),
		'core'
	)
	.'</p><p>'
	.__('Thanks<br/>---<br/>%1', array($cms_name), 'core')
	.'</p>';

send_mail(
	$user[ 'email' ],
	'no-reply@' . $_SERVER[ 'HTTP_HOST' ],
	__('Theme Approved'),
	$body,
	false
);

die( 'ok' );
?>
