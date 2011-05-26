<?php

/**
 * frontend/check_password.php, KV-Webme Privacy Plugin
 *
 * outputs "correct" if given password is correct
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../../ww.incs/basics.php';

$id = @$_SESSION[ 'userdata' ][ 'id' ];
$pass = @$_POST[ 'pass' ];
if($id == '' || $pass == '' )
	die( );

$pass = md5 ($pass);
$verify = dbRow( 'select password from user_accounts where id=' . $id );

if ($pass == $verify[ 'password' ])
	die( 'correct' );

?>
