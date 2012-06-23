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
	* register, and login
	*
	* @return array status
	*/
function Privacy_register() {
	$password=$_REQUEST['password'];
	$token=$_REQUEST['token'];
	$reg=@$_SESSION['privacy']['registration'];
	$email=@$reg['email'];
	$custom=@$reg['custom'];
	if (!is_array($custom)) {
		$custom=array();
	}
	$sql='select id from user_accounts where email="'.addslashes($email).'"';
	if (dbOne($sql, 'id')) {
		return array('error'=>__('already registered'));
	}
	if ($token && $token==@$reg['token']) {
		$latlngsql='';
		if (@$custom['_location']) {
			$latlng=dbRow(
				'select lat,lng from locations where id='.((int)$custom['_location'])
			);
			if ($latlng) {
				$latlngsql=',location_lat='.$latlng['lat'].',location_lng='
					.$latlng['lng'];
			}
		}
		$sql='insert into user_accounts set email="'.addslashes($email).'",'
			.'password=md5("'.addslashes($password).'"),active=1,date_created=now(),'
			.'extras="'.addslashes(json_encode($custom)).'"'.$latlngsql;
		dbQuery($sql);
		return array('ok'=>1);
	}
	else {
		return array('error'=>__('token does not match'));
	}
}
