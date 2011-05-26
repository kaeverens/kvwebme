<?php

/**
 * frontend/save_user_info.php, KV-Webme Privacy Plugin
 *
 * saves user info
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */


require '../../../ww.incs/basics.php';

$id = addslashes( @$_SESSION[ 'userdata' ][ 'id' ] );
$name = addslashes( @$_POST[ 'name' ] );
$phone = addslashes( @$_POST[ 'phone' ] );
$address = addslashes( @$_POST[ 'address' ] );
if( $id == 0 || $name == '' )
	exit;

dbQuery( 'update user_accounts set '
	. 'name="' . $name . '",'
	. 'phone="' . $phone . '",'
	. 'address="' . nl2br( $address ) . '" '
	. 'where id=' . $id
);

?>
