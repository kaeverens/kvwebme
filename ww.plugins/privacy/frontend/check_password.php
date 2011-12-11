<?php
/**
	* outputs "correct" if given password is correct
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require '../../../ww.incs/basics.php';

$id = @$_SESSION[ 'userdata' ][ 'id' ];
$pass = @$_POST[ 'pass' ];
if ($id == '' || $pass == '' ) {
	die( );
}

$pass = md5($pass);
$verify = dbRow('select password from user_accounts where id=' . $id);

if ($pass == $verify[ 'password' ]) {
	die('correct');
}
