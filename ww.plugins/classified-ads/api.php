<?php

// { ClassifiedAds_categoryTypesGet

/**
	* get prices for ads
	*
	* @return array
	*/
function ClassifiedAds_categoryTypesGet() {
	return dbAll('select * from classifiedads_types order by name');
}

// }
// { ClassifiedAds_categoriesGetAll

/**
	* get list of categories
	*
	* @return array
	*/
function ClassifiedAds_categoriesGetAll() {
	return dbAll('select * from classifiedads_categories order by name');
}

// }
// { ClassifiedAds_fileUpload

/**
	* upload a file
	*
	* @return status
	*/
function ClassifiedAds_fileUpload() {
	$id=(isset($_SESSION['userdata']['id']) && $_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$fname=USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload/'
		.$_FILES['Filedata']['name'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	@mkdir(dirname($fname), 0777, true);
	$from=$_FILES['Filedata']['tmp_name'];
	$dir=new DirectoryIterator(USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload');
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
	}
	move_uploaded_file($from, $fname);
	return array('ok'=>1);
}

// }
// { ClassifiedAds_advertiseThumbsGet

/**
	* list uploaded files
	*
	* @return status
	*/
function ClassifiedAds_advertiseThumbsGet() {
	$id=(isset($_SESSION['userdata']['id']) && $_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$dir=USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload/';
	$dir=new DirectoryIterator($dir);
	$images=array();
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		$images[]=$file->getFilename();
	}
	return array(
		'dir'=>'userfiles/'.$id.'/classified-ads-upload',
		'images'=>$images
	);
}

// }
// { ClassifiedAds_advertiseFileDelete

/**
	* delete uploaded files
	*
	* @return status
	*/
function ClassifiedAds_advertiseFileDelete() {
	$id=(isset($_SESSION['userdata']['id']) && $_SESSION['userdata']['id'])
		?$_SESSION['userdata']['id']
		:$_SESSION['tmpUID'];
	$fname=USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload/'
		.$_REQUEST['file'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	unlink($fname);
	@rmdir(USERBASE.'/f/userfiles/'.$id.'/classified-ads-upload');
	return array(
		'ok'=>1
	);
}

// }
// { ClassifiedAds_makePurchaseOrder

/**
	* make a purchase order
	*
	* @return null
	*/
function ClassifiedAds_makePurchaseOrder() {
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
		$dirname=USERBASE.'/f/userfiles/'.$user_id.'/classified-ads-upload';
		mkdir($dirname, 0777, true);
		$olddirname=USERBASE.'/f/userfiles/'.$_SESSION['tmpUID']
			.'/classified-ads-upload';
		$dir=new DirectoryIterator($olddirname);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$fname=$file->getFilename();
			rename($olddirname.'/'.$fname, $dirname.'/'.$fname);
		}
	}
	else {
		$user_id=$_SESSION['userdata']['id'];
		$dirname=USERBASE.'/f/userfiles/'.$user_id.'/classified-ads-upload';
	}
	$type_id=(int)$_REQUEST['type_id'];
	$days=(int)$_REQUEST['days'];
	$phone=$_REQUEST['phone'];
	$location=$_REQUEST['location'];
	$cost=$_REQUEST['cost'];
	$title=$_REQUEST['title'];
	$description=$_REQUEST['description'];
	dbQuery(
		'insert into classifiedads_purchase_orders set user_id='.$user_id
		.', type_id='.$type_id.', days='.$days.', title="'.addslashes($title).'"'
		.', phone="'.addslashes($phone).'", location="'.$addslashes($location).'"'
		.', cost="'.addslashes($cost).'"'
		.', description="'.addslashes($description).'"'
	);
	$ad_id=dbLastInsertId();
	$dir=new DirectoryIterator($dirname);
	mkdir($dirname.'/'.$ad_id, 0777, true);
	foreach ($dir as $file) {
		if ($file->isDot() || $file->isDir()) {
			continue;
		}
		$fname=$file->getFilename();
		rename($dirname.'/'.$fname, $dirname.'/'.$ad_id.'/'.$fname);
	}
	return array('id'=>dbLastInsertId());
}

// }
