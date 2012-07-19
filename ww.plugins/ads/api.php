<?php

function Ads_typeGet() {
	return dbRow('select * from ads_types where id='.((int)$_REQUEST['id']));
}
function Ads_typesGet() {
	return dbAll('select * from ads_types order by name');
}
// { Ads_fileUpload

/**
	* upload a file
	*
	* @return status
	*/
function Ads_fileUpload() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$id=$_SESSION['userdata']['id'];
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
function Ads_getTmpImage() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$id=$_SESSION['userdata']['id'];
	$url=false;
	$dir=new DirectoryIterator(USERBASE.'/f/userfiles/'.$id.'/ads-upload');
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		$url='userfiles/'.$id.'/ads-upload/'.$file->getFilename();
	}
	return $url;
}
function Ads_makePurchaseOrder() {
	if (!isset($_SESSION['userdata']['id'])) {
		return array('error'=>__('not logged in'));
	}
	$user_id=$_SESSION['userdata']['id'];
	$type_id=(int)$_REQUEST['type_id'];
	$days=(int)$_REQUEST['days'];
	$target_url=$_REQUEST['target_url'];
	dbQuery(
		'insert into ads_purchase_orders set user_id='.$user_id.', type_id='
		.$type_id.', days='.$days.', target_url="'.addslashes($target_url).'"'
	);
	return array('id'=>dbLastInsertId());
}
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
	exit;
}
