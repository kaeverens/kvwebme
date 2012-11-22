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

// { Core_directoryCheckName

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
		Core_quit();
	}
	if (!file_exists($file) || !is_dir($file)) {
		header('HTTP/1.0 404 Not Found');
		echo 'directory does not exist';
		Core_quit();
	}
}

// }
// { Core_fileCheckName

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
		Core_quit();
	}
	if (!file_exists($file) || !is_file($file)) {
		header('HTTP/1.0 404 Not Found');
		echo 'file does not exist';
		Core_quit();
	}
}

// }
// { Core_getFileList

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

// }
// { Core_getFileInfo

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

// }
// { Core_getImg

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
			header("HTTP/1.0 404 Not Found");
			echo 'file does not exist';
			Core_quit();
		}
	}
	else {
		$f=USERBASE.'/f/'.$_REQUEST['_remainder'];
		if (!file_exists($f)) { 
			header("HTTP/1.0 404 Not Found");
			echo 'file does not exist';
			Core_quit();
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
			Core_quit();
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
	Core_quit();
}

// }
// { Core_getMenu

/**
	* retrieve list of pages for a menu
	*
	* @return array array of menu items
	*/
function Core_getMenu() {
	require_once 'menus.php';
	return array(
		@$_REQUEST['pid'],
		Menu_getChildren(
			@$_REQUEST['pid'],
			@$_REQUEST['id'],
			0,
			@$_REQUEST['top_id'],
			0
		)
	);
}

// }
// { Core_getUserData

/**
	* get details of the logged-in user
	*
	* @return array of details
	*/
function Core_getUserData() {
	if (!isset($_SESSION['userdata'])) { // not logged in
		return array('error'=>'you are not logged in');
	}
	// { main user row
	$sql='select id, name, email, address, parent, extras, last_login'
		.', last_view, date_created from user_accounts'
		.' where id='.$_SESSION['userdata']['id'].' limit 1';
	$user=dbRow($sql);
	// }
	// { address
	$json=json_decode($user['address'], true);
	$user['address']=array();
	if (is_array($json)) {
		foreach ($json as $v) {
			$user['address'][]=$v;
		}
	}
	// }
	// { extras
	$user['extras']=@$user['extras']
		?json_decode($user['extras'], true)
		:array();
	// }
	// { groups
	$groups=dbAll(
		'select groups_id from users_groups where user_accounts_id='
		.$_SESSION['userdata']['id']
	);
	$g=array();
	foreach ($groups as $group) {
		array_push($g, $group['groups_id']);
	}
	$user['groups']=$g;
	// }
	return $user;
}

// }
// { Core_languagesAddStrings

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
	$added=array();
	foreach ($_REQUEST['strings'] as $str) {
		$sql='select lang from languages where str="'.addslashes($str[0]).'"'
			.' and context="'.addslashes($str[1]).'"';
		if (dbOne($sql, 'lang')) {
			continue;
		}
		dbQuery(
			'insert into languages set str="'.addslashes($str[0]).'",'
			.'context="'.addslashes($str[1]).'",'
			.'trstr="'.addslashes($str[0]).'",'
			.'lang="'.addslashes($_languages[0]).'"'
		);
		$added[]=$str[0];
		Core_cacheClear('languages');
	}
	return $added;
}

// }
// { Core_languagesGet

/**
	* return a list of the site's languages
	*
	* @return array the list of languages
	*/
function Core_languagesGet() {
	$sql='select * from language_names order by is_default desc, name';
	$names=Core_cacheLoad('languages', 'languagenames', -1);
	if ($names===-1) {
		$names=dbAll($sql);
		Core_cacheSave('languages', 'languagenames', $names);
	}
	return $names;
}

// }
// { Core_locationsGet

/**
	* return a list of locations recorded by the CMS
	*
	* @return array the list of locations
	*/
function Core_locationsGet() {
	$pid=isset($_REQUEST['pid'])?(int)$_REQUEST['pid']:-1;
	$locs=Core_cacheLoad('core', 'locations,'.$pid, -1);
	if ($locs == -1) {
		$filter=$pid>-1?'where parent_id='.$pid:'';
		$locs=dbAll(
			'select * from locations '.$filter.' order by is_default desc, name'
		);
		Core_cacheSave('core', 'locations,'.$pid, $locs);
	}
	return $locs;
}

// }
// { Core_locationsGetFull

/**
	* return a list of locations recorded by the CMS, with parents
	*
	* @return array the list of locations
	*/
function Core_locationsGetFull() {
	$locs=Core_cacheLoad('core', 'locationsFull', -1);
	if ($locs == -1) {
		$locs=dbAll('select * from locations order by is_default desc, name');
		// { getParents

		/**
			* get list of sub-locations, recursive
			*
			* @param array $locs cache
			* @param int   $id   locations parent
			*
			* @return list of sub-locations
			*/
		function getParents($locs, $id) {
			if (!$id) {
				return '';
			}
			foreach ($locs as $loc) {
				if ($loc['id']==$id) {
					return getParents($locs, $loc['parent_id']).' / '.$loc['name'];
				}
			}
			return '';
		}

		// }
		$arr=array();
		foreach ($locs as $k=>$v) {
			$locs[$k]['path']=preg_replace(
				'/^ \/ /', '', getParents($locs, $v['id'])
			);
			$arr[$locs[$k]['path']]=$v['id'];
		}
		$locs=$arr;
		ksort($locs);
		Core_cacheSave('core', 'locationsFull', $locs);
	}
	return $locs;
}

// }
// { Core_login

/**
	* log in
	*
	* @return null
	*/
function Core_login() {
	// { variables
	if (!isset($_REQUEST['email']) || !isset($_REQUEST['password'])) {
		Core_quit(
			'{"error":"'.addslashes(
				__('missing email address or password')
			).'"}'
		);
	}
	$email=$_REQUEST['email'];
	$password=$_REQUEST['password'];
	// }
	$sql='select * from user_accounts where email="'.addslashes($email)
		.'" and password=md5("'.$password.'") and active';
	$r=dbRow($sql);
	if ($r && count($r)) {
		$r['password']=$password;
		$_SESSION['userdata']=$r;
		dbQuery('update user_accounts set last_login=now() where id='.$r['id']);
		$ret=array('ok'=>1);
		if (isset($_REQUEST['return_groups'])) {
			$groups=dbAll(
				'select groups_id as id,name from users_groups,groups'
				.' where groups.id=groups_id'
				.' and user_accounts_id='.$_SESSION['userdata']['id']
			);
			$ret['groups']=$groups;
		}
		Core_quit(json_encode($ret));
	}
	Core_quit('{"error":"either the email address or the password are incorrect"}');
}

// }
// { Core_logout

/**
	* log out
	*
	* @return null
	*/
function Core_logout() {
	unset($_SESSION['userdata']);
	return array(
		'ok'=>1
	);
}

// }
// { Core_nothing

/**
	* does nothing... :-)
	* useful for simply keeping the session alive
	*
	* @return null
	*/
function Core_nothing() {
	return array();
}

// }
// { Core_qrCode

/**
	* create a QR code
	*
	* @return null
	*/
function Core_qrCode() {
	$id=(int)@$_REQUEST['id'];
	$fname=USERBASE.'/ww.cache/pages/qrcode'.$id;
	if (!file_exists($fname)) {
		$page=Page::getInstance($id);
		require_once 'phpqrcode.php';
		QRcode::png(
			$page->getAbsoluteUrl(),
			$fname
		);
	}
	header('Content-type: image/png');
	header('Cache-Control: max-age=2592000, public');
	header('Expires-Active: On');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	header('Content-Length: ' . filesize($fname));
	readfile($fname);
	Core_quit();
}

// }
// { Core_register

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
		if (isset($_REQUEST['custom']) && is_array($_REQUEST['custom'])) {
			foreach ($_REQUEST['custom'] as $k=>$v) {
				$custom[$k]=$v;
			}
		}
		if (@$custom['_location']) {
			$latlng=dbRow(
				'select lat,lng from locations where id='.((int)$custom['_location'])
			);
			if ($latlng) {
				$latlngsql=',location_lat='.$latlng['lat'].',location_lng='
					.$latlng['lng'];
			}
		}
		$name=@$_REQUEST['name']?' name="'.addslashes($_REQUEST['name']).'",':'';
		$sql='insert into user_accounts set '.$name.'email="'.addslashes($email).'",'
			.'password=md5("'.addslashes($password).'"),active=1,date_created=now(),'
			.'extras="'.addslashes(json_encode($custom)).'"'.$latlngsql;
		dbQuery($sql);
		return array('ok'=>1);
	}
	else {
		return array('error'=>'token does not match');
	}
}

// }
// { Core_sendLoginToken

/**
	* request a login token to be sent out to your email address
	*
	* @return null
	*/
function Core_sendLoginToken() {
	if (!isset($_REQUEST['email'])) {
		return array(
			'error'=>__('No email address was entered.')
		);
	}
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array(
			'error'=>__('Please enter a properly formatted email address.')
		);
	}
	$u=dbRow("SELECT * FROM user_accounts WHERE email='$email'");
	if ($u && count($u)) {
		$token=md5(time().'|'.rand());
		dbQuery(
			"UPDATE user_accounts SET verification_hash='$token' "
			."WHERE email='$email'"
		);
		Core_mail(
			$email, '['.$_SERVER['HTTP_HOST'].'] user password token',
			'Your token is: '.$token,
			$email
		);
		Core_quit('{"ok":1}');
	}
	Core_quit('{"error":"that email address not found in the users table"}');
}

// }
// { Core_sendRegistrationToken

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
	Core_trigger('user-registration-token-sent');
	$_SESSION['privacy']['registration']=array(
		'token'         => rand(10000, 99999),
		'custom'        => array(),
		'email'         => $email
	);
	if (@$_REQUEST['custom'] && is_array($_REQUEST['custom'])) {
		$_SESSION['privacy']['registration']['custom']=$_REQUEST['custom'];
	}
	$emaildomain=str_replace('www.', '', $_SERVER['HTTP_HOST']);
	$from=Core_siteVar('useraccounts_registrationtokenemail_from');
	Core_mail(
		$email,
		Core_siteVar('useraccounts_registrationtokenemail_subject'),
		str_replace(
			'%token%',
			$_SESSION['privacy']['registration']['token'],
			Core_siteVar('useraccounts_registrationtokenemail_message')
		),
		$from
	);
	return array('ok'=>1);
}

// }
// { Core_updateUserPasswordUsingToken

/**
	* update a password, using a verification code
	*
	* @return null
	*/
function Core_updateUserPasswordUsingToken() {
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		Core_quit('{"error":"please enter a properly formatted email address"}');
	}
	$token=addslashes($_REQUEST['token']);
	if ($token=='') {
		Core_quit('{"error":"no token entered"}');
	}
	$password=$_REQUEST['password'];
	if ($password=='') {
		Core_quit('{"error":"no new password entered"}');
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
		Core_quit('{"ok":1}');
	}
	Core_quit('{"error":"user not found, or verification token is out of date"}');
}

// }
// { Core_usersAvatarsGet

/**
	* get a list of users' avatars
	*
	* @return status
	*/
function Core_usersAvatarsGet() {
	if (!isset($_REQUEST['ids']) || !is_array($_REQUEST['ids'])) {
		return array();
	}
	$ids=$_REQUEST['ids'];
	foreach ($ids as $k=>$v) {
		$ids[$k]=(int)$v;
	}
	$sql='select id,avatar from user_accounts where id in ('.join(', ', $ids).')'
		.' and avatar is not null';
	return dbAll($sql);
}

// }
// { Core_userSetAvatar

/**
	* update the user's avatar
	*
	* @return status
	*/
function Core_userSetAvatar() {
	$src=$_REQUEST['src'];
	if (!isset($_SESSION['userdata'])) { // not logged in
		return array('error'=>'you are not logged in');
	}
	dbQuery(
		'update user_accounts set avatar="'.addslashes($src)
		.'" where id='.$_SESSION['userdata']['id']
	);
	return true;
}

// }
// { Core_userSetDefaultAddress

/**
	* update the default address of a user
	*
	* @return status
	*/
function Core_userSetDefaultAddress() {
	$aid=(int)@$_REQUEST['aid'];
	if (!isset($_SESSION['userdata'])) { // not logged in
		return array('error'=>'you are not logged in');
	}
	$addresses=dbOne(
		'select id,address from user_accounts where id='
		.$_SESSION['userdata']['id'].' limit 1',
		'address'
	);
	if (!$addresses) {
		$addresses='[]';
	}
	$addresses=json_decode($addresses);
	foreach ($addresses as $k=>$v) {
		if ($k==$aid) {
			$addresses[$k]->default='yes';
			continue;
		}
		$addresses[$k]->default='no';
	}
	dbQuery(
		'update user_accounts set address="'.addslashes(json_encode($addresses))
		.'" where id='.$_SESSION['userdata']['id'].' limit 1'
	);
	return true;
}

// }
// { Core_userGetUid

/**
	* get the user id of a user
	*
	* @return status of user
	*/
function Core_userGetUid() {
	$email=@$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>__('invalid email address'));
	}
	$sql='select id from user_accounts where email="'.addslashes($email).'"';
	$id=(int)dbOne($sql, 'id');
	if (!$id) {
		$id=isset($_SESSION['tmpUID'])
			?$_SESSION['tmpUID']
			:'tmp-'.md5(microtime(true));
		$_SESSION['tmpUID']=$id;
	}
	return array('uid'=>$id);
}
// }
// { Core_translationsGet

/**
	* get all available translations for a particular context
	*
	* @return array of tanslations
	*/
function Core_translationsGet() {
	global $_languages;
	$context=@$_REQUEST['context'];
	$md5=md5(join('|', $_languages).'|'.$context);
	$strings=Core_cacheLoad('core-translation', $md5);
	if (1 || $strings==false) {
		$strings=array();
		for ($i=count($_languages)-1;$i>=0;--$i) {
			$sql='select * from languages where lang="'.$_languages[$i]
				.'" and context="'.addslashes($context).'"';
			$md5=md5($sql);
			$rs=Core_cacheLoad('languages', $md5, -1);
			if ($rs===-1) {
				$rs=dbAll($sql);
				Core_cacheSave('languages', $md5, $rs);
			}
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

// }
