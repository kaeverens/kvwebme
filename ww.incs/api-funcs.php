<?php

/**
	* API for common non-admin WebME functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

function Core_nothing() {
	return array();
}
function Core_getFileInfo() {
	if (!isset($_REQUEST['src'])) {
		return array('error'=>'missing src');
	}
	$file=USERBASE.$_REQUEST['src'];
	if (strpos($file, '..')!==false
		|| (strpos($file, '/.')!==false
		&& strpos(preg_replace('#/\.files/#', '/', $file), '/.')!==false)
	) {
		exit;
	}
	if (!file_exists($file) || !is_file($file)) {
		header('HTTP/1.0 404 Not Found');
		echo 'file does not exist';
		exit;
	}
	
	$finfo=finfo_open(FILEINFO_MIME_TYPE);
	$mime=finfo_file($finfo, $file);
	
	return array(
		'mime'=>$mime
	);
}
function Core_getUserData() {
	if (!isset($_SESSION['userdata'])) { // not logged in
		return array('error'=>'you are not logged in');
	}
	$user=dbRow(
		'select id,name,email,phone,address,parent,extras,last_login,last_view,da'
		.'te_created from user_accounts where id='.$_SESSION['userdata']['id']
		.' limit 1'
	);
	$user['address']=json_decode($user['address'], true);
	$user['extras']=@$user['extras']
		?json_decode($user['extras'], true)
		:array();
	$groups=dbAll(
		'select groups_id from users_groups where user_accounts_id='
		.$_SESSION['userdata']['id']
	);
	$g=array();
	foreach ($groups as $group) {
		array_push($g, $group['groups_id']);
	}
	$user['groups']=$g;
	return $user;
}
function Core_login() {
	// { variables
	$email=$_REQUEST['email'];
	$password=$_REQUEST['password'];
	// }
	$r=dbRow(
		'select * from user_accounts where email="'.addslashes($email)
		.'" and password=md5("'.$password.'")'
	);
	if ($r && count($r)) {
		$r['password']=$password;
		$_SESSION['userdata'] = $r;
		dbQuery('update user_accounts set last_login=now() where id='.$r['id']);
		exit('{"ok":1}');
	}
	exit('{"error":"either the email address or the password are incorrect"}');
}
function Core_logout() {
	unset($_SESSION['userdata']);
}
function Core_sendLoginToken() {
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		exit('{"error":"please enter a properly formatted email address"}');
	}
	$u=dbRow("SELECT * FROM user_accounts WHERE email='$email'");
	if ($u && count($u)) {
		$token=md5(time().'|'.rand());
		dbQuery(
			"UPDATE user_accounts SET verification_hash='$token' "
			."WHERE email='$email'"
		);
		mail(
			$email, '['.$_SERVER['HTTP_HOST'].'] user password token',
			'Your token is: '.$token,
			"Reply-to: $email\nFrom: $email"
		);
		exit('{"ok":1}');
	}
	exit('{"error":"that email address not found in the users table"}');
}
function Core_updateUserPasswordUsingToken() {
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		exit('{"error":"please enter a properly formatted email address"}');
	}
	$token=addslashes($_REQUEST['token']);
	if ($token=='') {
		exit('{"error":"no token entered"}');
	}
	$password=$_REQUEST['password'];
	if ($password=='') {
		exit('{"error":"no new password entered"}');
	}
	$u=dbRow(
		"SELECT * FROM user_accounts WHERE email='$email' "
		."and verification_hash='$token'"
	);
	if ($u && count($u)) {
		$password=md5($password);
		dbQuery(
			"UPDATE user_accounts SET password='$password',"
			."verification_hash='' WHERE email='$email'"
		);
		exit('{"ok":1}');
	}
	exit('{"error":"user not found, or verification token is out of date"}');
}
