<?php
/**
	* API for Ads plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Ads_adsGetMy

/**
	* get my ads
	*
	* @return array of my ads
	*/
function Ads_adsGetMy() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	return dbAll(
		'select * from ads where customer_id='.$_SESSION['userdata']['id']
	);
}

// }
// { Ads_statsGet

/**
	* get view and click statistics
	*
	* @return array stats
	*/
function Ads_statsGet() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$ad_id=(int)$_REQUEST['ad'];
	$from=$_REQUEST['from'];
	$to=$_REQUEST['to'];
	$sql='select * from ads_track where cdate>"'.addslashes($from).'"'
		.' and cdate<"'.addslashes($to).' 24"';
	if ($ad_id) {
		$sql.=' and ad_id='.$ad_id;
	}
	else {
		$rs=dbAll('select id from ads where customer_id='.$_SESSION['userdata']['id']);
		$ids=array();
		foreach ($rs as $r) {
			$ids[]=$r['id'];
		}
		$sql.=' and ad_id in ('.join(',', $ids).')';
	}
	return dbAll($sql);
}

// }
// { Ads_typeGet

/**
	* get details about a specific ad type
	*
	* @return array details
	*/
function Ads_typeGet() {
	return dbRow('select * from ads_types where id='.((int)$_REQUEST['id']));
}

// }
// { Ads_typesGet

/**
	* get all ad types
	*
	* @return list of ad types
	*/
function Ads_typesGet() {
	return dbAll('select * from ads_types order by name');
}

// }
// { Ads_fileUpload

/**
	* upload a file
	*
	* @return status
	*/
function Ads_fileUpload() {
	$id=isset($_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$fname=USERBASE.'/f/userfiles/'.$id.'/ads-upload/'.$_FILES['Filedata']['name'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	@mkdir(dirname($fname), 0777, true);
	$from=$_FILES['Filedata']['tmp_name'];
	$dir=new DirectoryIterator(USERBASE.'/f/userfiles/'.$id.'/ads-upload');
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		unlink(USERBASE.'/f/userfiles/'.$id.'/ads-upload/'.$file->getFilename());
	}
	move_uploaded_file($from, $fname);
	return array('ok'=>1);
}

// }
// { Ads_posterUpload

/**
	* upload a poster
	*
	* @return status
	*/
function Ads_posterUpload() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$id=$_SESSION['userdata']['id'];
	$fname=USERBASE.'/f/userfiles/'.$id.'/ads-upload-poster/'
		.$_FILES['Filedata']['name'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	@mkdir(dirname($fname), 0777, true);
	$from=$_FILES['Filedata']['tmp_name'];
	$dir=new DirectoryIterator(USERBASE.'/f/userfiles/'.$id.'/ads-upload-poster');
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		unlink(USERBASE.'/f/userfiles/'.$id.'/ads-upload-poster/'.$file->getFilename());
	}
	move_uploaded_file($from, $fname);
	return array('ok'=>1);
}

// }
// { Ads_getTmpImage

/**
	* get a temporary image
	*
	* @return url of the temporary image
	*/
function Ads_getTmpImage() {
	$id=isset($_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$dirname=USERBASE.'/f/userfiles/'.$id.'/ads-upload';
	if (!file_exists($dirname)) {
		mkdir($dirname, 0777, true);
	}
	$dir=new DirectoryIterator($dirname);
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		return 'userfiles/'.$id.'/ads-upload/'.$file->getFilename();
	}
	return false;
}

// }
// { Ads_makePurchaseOrder

/**
	* make a purchase order
	*
	* @return null
	*/
function Ads_makePurchaseOrder() {
	if (!isset($_SESSION['userdata']['id'])) {
		$email=$_REQUEST['email'];
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return array('error'=>__('invalid email address'));
		}
		dbQuery(
			'insert into user_accounts set email="'.addslashes($email).'",'
			.'name="'.addslashes($email).'",active=1,date_created=now()'
		);
		$user_id=dbLastInsertId();
		$dirname=USERBASE.'/f/userfiles/'.$user_id.'/ads-upload';
		mkdir($dirname, 0777, true);
		$olddirname=USERBASE.'/f/userfiles/'.$_SESSION['tmpUID'].'/ads-upload';
		$dir=new DirectoryIterator($olddirname);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$fname=$file->getFilename();
			copy($olddirname.'/'.$fname, $dirname.'/'.$fname);
		}
	}
	else {
		$user_id=$_SESSION['userdata']['id'];
	}
	$type_id=(int)$_REQUEST['type_id'];
	$days=(int)$_REQUEST['days'];
	$target_url=$_REQUEST['target_url'];
	$target_type=(int)$_REQUEST['target_type'];
	dbQuery(
		'insert into ads_purchase_orders set user_id='.$user_id.', type_id='
		.$type_id.', days='.$days.', target_url="'.addslashes($target_url).'"'
		.', target_type='.$target_type
	);
	return array('id'=>dbLastInsertId());
}

// }
// { Ads_track

/**
	* track an ad view
	*
	* @return null
	*/
function Ads_track() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select * from ads where id='.$id);
	if (!$r) {
		return false;
	}
	dbQuery('insert into ads_track set ad_id='.$id.', click=1, cdate=now()');
}

// }
// { Ads_go

/**
	* follow an ad
	*
	* @return null
	*/
function Ads_go() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select * from ads where id='.$id);
	if (!$r) {
		return false;
	}
	dbQuery('insert into ads_track set ad_id='.$id.', click=1, cdate=now()');
	if (strpos($r['target_url'], 'www.')===0) {
		$r['target_url']='http://'.$r['target_url'];
	}
	header('Location: '. $r['target_url']);
	Core_quit();
}

// }
