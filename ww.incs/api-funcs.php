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

/**
	* does nothing... :-)
	*
	* @return null
	*/
function Core_nothing() {
	return array();
}

/**
	* return a list of the site's languages
	*
	* @return array the list of languages
	*/
function Core_languagesGet() {
	return dbAll('select * from language_names order by is_default desc, name');
}

/**
	* return a list of locations recorded by the CMS
	*
	* @return array the list of locations
	*/
function Core_locationsGet() {
	$locs=Core_cacheLoad('core', 'locations', -1);
	if ($locs == -1) {
		$locs=dbAll('select * from locations order by is_default desc, name');
		Core_cacheSave('core', 'locations', $locs);
	}
	return $locs;
}

/**
	* check that a directory exists
	*
	* @param string $file directory to check
	*
	* @return null
	*/
function Core_directoryCheckName($file) {
	if (strpos($file, '..')!==false
		|| (strpos($file, '/.')!==false
		&& strpos(preg_replace('#/\.files/#', '/', $file), '/.')!==false)
	) {
		exit;
	}
	if (!file_exists($file) || !is_dir($file)) {
		header('HTTP/1.0 404 Not Found');
		echo 'directory does not exist';
		exit;
	}
}

/**
	* check that a file exists
	*
	* @param string $file file to check
	*
	* @return null
	*/
function Core_fileCheckName($file) {
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
}

/**
	* get list of files in a directory
	*
	* @return array of files
	*/
function Core_getFileList() {
	if (!isset($_REQUEST['src'])) {
		return array('error'=>'missing src');
	}
	$dir=USERBASE.'/'.$_REQUEST['src'];
	Core_directoryCheckName($dir);
	$files=array();
	$dir=new DirectoryIterator($dir);
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		$files[]=array(
			'n'=>$file->getFilename(),
			't'=>$file->isDir()?'d':'f'
		);
	}
	return $files;
}

/**
	* get mime data about a file
	*
	* @return data about the file
	*/
function Core_getFileInfo() {
	if (!isset($_REQUEST['src'])) {
		return array('error'=>'missing src');
	}
	$file=USERBASE.'/'.$_REQUEST['src'];
	Core_fileCheckName($file);

	$finfo=finfo_open(FILEINFO_MIME_TYPE);
	$mime=finfo_file($finfo, $file);
	
	return array(
		'mime'=>$mime
	);
}

/**
	* retrieve an image
	*
	* @return null
	*/
function Core_getImg() {
	$w=isset($_REQUEST['w'])?(int)$_REQUEST['w']:0;
	$h=isset($_REQUEST['h'])?(int)$_REQUEST['h']:0;
	if (isset($_REQUEST['base64'])) {
		$f=base64_decode($_REQUEST['base64']);
		if (@fopen($f, 'r')!=true) {
			echo 'file "'.$f.'" does not exist';
			exit;
		}
	}
	else {
		$f=USERBASE.'/f/'.$_REQUEST['_remainder'];
		if (!file_exists($f)) {
			echo 'file "'.$f.'" does not exist';
			exit;
		}
	}
	$ext=strtolower(preg_replace('/.*\./', '', $f));
	switch ($ext) {
		case 'jpg': case 'jpe': // {
			$ext='jpeg';
		break; // }
		case 'png': case 'gif': case 'jpeg': // {
		break; // }
		default: // {
			echo 'unhandled image extension '.$ext;
			exit;
			// }
	}
	if (strpos($f, '/.')!=false) {
		return false; // hack attempt
	}
	if ($w || $h) {
		list($width, $height)=getimagesize($f);
		$resize=0;
		if ($w && $width>$w) {
			$height*=$w/$width;
			$width=$w;
			$resize=1;
		}
		if ($h && $height>$h) {
			$width*=$h/$height;
			$height=$h;
			$resize=1;
		}
		if ($resize) {
			$width=(int)$width;
			$height=(int)$height;
			@mkdir(USERBASE.'/ww.cache/resized.images');
			$c=USERBASE.'/ww.cache/resized.images/'.md5($f).','.$width.'x'.$height
				.'.png';
			if (!file_exists($c) || filesize($c)==0) {
				CoreGraphics::resize($f, $c, $width, $height);
			}
			$f=$c;
			$ext='png';
		}
	}
	header('Content-type: image/'.$ext);
	header('Cache-Control: max-age=2592000, public');
	header('Expires-Active: On');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	header('Content-Length: ' . filesize($f));
	readfile($f);
	exit;
}

/**
	* retrieve list of pages for a menu
	*
	* @return array array of menu items
	*/
function Core_getMenu() {
	require_once 'menus.php';
	header('Expires-Active: On');
	header('Cache-Control: max-age = 9999999999');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	return array(
		$_REQUEST['pid'],
		Menu_getChildren(
			$_REQUEST['pid'],
			$_REQUEST['id'],
			0,
			$_REQUEST['top_id'],
			0
		)
	);
}

/**
	* get details of the logged-in user
	*
	* @return array of details
	*/
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

/**
	* add a number of strings to the languages table
	*
	* @return status
	*/
function Core_languagesAddStrings() {
	global $_languages;
	if (!@$_SESSION['wasAdmin']) {
		return;
	}
	foreach ($_REQUEST['strings'] as $str) {
		dbQuery(
			'insert into languages set str="'.addslashes($str[0]).'",'
			.'context="'.addslashes($str[1]).'",'
			.'trstr="'.addslashes($str[0]).'",'
			.'lang="'.addslashes($_languages[0]).'"'
		);
	}
}

/**
	* log in
	*
	* @return null
	*/
function Core_login() {
	// { variables
	if (!isset($_REQUEST['email']) || !isset($_REQUEST['password'])) {
		exit(
			'{"error":"'.addslashes(
				__('missing email address or password')
			).'"}'
		);
	}
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

/**
	* log out
	*
	* @return null
	*/
function Core_logout() {
	unset($_SESSION['userdata']);
}

/**
	* request a login token to be sent out to your email address
	*
	* @return null
	*/
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

/**
	* update a password, using a verification code
	*
	* @return null
	*/
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

/**
	* get all available translations for a particular context
	*
	* @return array of tanslations
	*/
function Core_translationsGet() {
	global $_languages;
	$context=$_REQUEST['context'];
	$md5=md5(join('|', $_languages).'|'.$context);
	$strings=Core_cacheLoad('core-translation', $md5);
	if (1 || $strings==false) {
		$strings=array();
		for ($i=count($_languages)-1;$i>=0;--$i) {
			$rs=dbAll(
				'select * from languages where lang="'.$_languages[$i]
				.'" and context="'.addslashes($context).'"'
			);
			foreach ($rs as $r) {
				$strings[$r['str']]=$r['trstr'];
			}
		}
		Core_cacheSave('core-translation', $md5, $strings);
	}
	return array(
		'context'=>$context,
		'strings'=>$strings
	);
}

/**
	* send registration token
	*
	* @return array status
	*/
function Core_sendRegistrationToken() {
	$email=@$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>'invalid email address');
	}
	$sql='select id from user_accounts where email="'.addslashes($email).'"';
	if (dbOne($sql, 'id')) {
		return array('error'=>'already registered');
	}
	if (!isset($_SESSION['privacy'])) {
		$_SESSION['privacy']=array();
	}
	$_SESSION['privacy']['registration']=array(
		'token'         => rand(10000, 99999),
		'custom'        => array(),
		'email'         => $email
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

/**
	* register, and login
	*
	* @return array status
	*/
function Core_register() {
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
		return array('error'=>'already registered');
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
		return array('error'=>'token does not match');
	}
}
