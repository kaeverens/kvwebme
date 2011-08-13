<?php

/**
 * frontend/save_password.php, KV-Webme Privacy Plugin
 *
 * saves the given password
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../../ww.incs/basics.php';

$id = @$_SESSION[ 'userdata' ][ 'id' ];
$pass = @$_POST[ 'pass' ];
$match = @$_POST[ 'match' ];
if($id == '' || $pass == '' || $match == '')
	die( );

if( $pass != $match )
	die( );

$pass = md5 ($pass);
dbQuery( 'update user_accounts set password="' . $pass . '" where id=' . $id );
