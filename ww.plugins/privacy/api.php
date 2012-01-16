<?php
/**
	* API for Privacy plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* login
	*
	* @return null
	*/
function Privacy_login() {
	$no_redirect=1;
	$_REQUEST['action']='login';
	require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/user-authentication.php';
	if (isset($_SESSION['userdata']) && $_SESSION['userdata']['id']) {
		return array(
			'redirect'=>isset($redirect_url)?$redirect_url:''
		);
	}
	return array(
		'error'=>'incorrect email or password'
	);
}

/**
	* send registration token
	*
	* @return null
	*/
function Privacy_sendRegistrationToken() {
	$email=@$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>'invalid email address');
	}
	if (!isset($_SESSION['privacy'])) {
		$_SESSION['privacy']=array();
	}
	$_SESSION['privacy']['registration']=array(
		'token'         => rand(10000, 99999),
		'token_matched' => false,
		'custom'        => array()
	);
	if (@$_REQUEST['custom'] && is_array($_REQUEST['custom'])) {
		$_SESSION['privacy']['registration']['custom']=$_REQUEST['custom'];
	}
	$emaildomain=str_replace('www.', '', $_SERVER['HTTP_HOST']);
	mail(
		$email,
		'['.$_SERVER['HTTP_HOST'].'] user registration',
		'Your token is: '.$_SESSION['privacy']['registration']['token'],
		"Reply-to: info@".$emaildomain."\nFrom: info@".$emaildomain
	);
	return array('ok'=>1);
}
