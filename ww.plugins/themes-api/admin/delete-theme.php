<?php

/**
 * admin/delete-theme.php, KV-Webme Themes Repository
 *
 * accessed via AJAX, this file deletes a theme from
 * the repository and informs the author
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$theme_id = addslashes( @$_POST[ 'theme_id' ] );
$user_id = addslashes( @$_POST[ 'user_id' ] );

if( $theme_id == 0 || $user_id == 0 )
	die( 'error' );

require '../../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.incs/mail.php';

/**
 * get data on the theme and the user
 */
$user = dbRow( 'select name,email from user_accounts where id=' . $user_id );
$theme_name = dbOne( 'select name from themes_api where id=' . $theme_id, 'name' );

/**
 * delete theme from user-files
 * and from themes-personal
 */
shell_exec( 'rm -rf ' . USERBASE . 'f/themes_api/themes/' . $theme_id );
shell_exec( 'rm -rf ' . USERBASE . 'themes-personal/' . $theme_name );

/**
 * delete the theme from the db
 */
dbQuery( 'delete from  themes_api where id=' . $theme_id );

/**
 * send the user an email telling them the theme
 * was deleted
 */
$body = ' 
<h3>Theme Deleted</h3>
<p>Hi ' . $user[ 'name' ] . ',</p>
<p>Your theme named "' . $theme_name . '" has been deleted by moderaters.</p>
<p>Thanks<br/>---<br/>
KvWebme</p>
';
send_mail( $user[ 'email' ], 'noreply@' . $_SERVER[ 'HTTP_HOST' ], 'Theme Deleted', $body, false );

die( 'ok' );
?>
