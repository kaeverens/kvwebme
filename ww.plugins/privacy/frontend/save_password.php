<?php
/**
	* saves the given password
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

require_once '../../../ww.incs/basics.php';

$id = (int)@$_SESSION['userdata']['id'];
$pass = @$_POST['pass'];
$match = @$_POST['match'];
if (!$id || $pass == '' || $pass != $match) {
	die();
}

$pass=md5($pass);
dbQuery('update user_accounts set password="'.$pass.'" where id='.$id);
