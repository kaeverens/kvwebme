<?php
/**
	* OnlineStore api functions
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { OnlineStore_checkQrCode

/**
	* check a QR Code voucher to see if it's valid
	*
	* @return null
	*/
function OnlineStore_checkQrCode() {
	global $DBVARS;
	echo '<table style="width:100%"><tr><td><img src="/f/skin_files/logo.png"/>'
		.'</td><td><h1>'.$DBVARS['site_title'].'</h1><h3>'
		.$DBVARS['site_subtitle'].'</h3></td></tr></table><hr/>';
	$oid=(int)@$_REQUEST['oid'];
	$pid=@$_REQUEST['pid'];
	if (!$oid || !$pid) {
		echo 'product or order ID not found';
		exit;
	}
	$order=dbRow('select * from online_store_orders where id='.$oid);
	if (!$order) {
		echo 'order ID not found.';
		exit;
	}
	$md5=$_REQUEST['md5'];
	if ($md5!=md5($order['invoice'])) {
		echo 'MD5 check failed. this voucher has been tampered with.';
		exit;
	}
	echo '<h1>Valid Voucher</h1>';
	$items=json_decode($order['items'], true);
	$item=$items[$pid];
	echo '<h2>'.$item['short_desc'].'</h2>'.$item['long_desc'];
	if (!isset($item['voucher_redeemed'])) {
		echo '<em>This voucher has not yet been redeemed. To redeem this voucher,'
			.' please hand it in to the retailer with your purchase.</em>';
	}
	else {
		echo '<p style="text-decoration:underline;color:red"><strong style="tex'
			.'t-decoration:blink">warning</strong>: this voucher has already been'
			.' redeemed.</p>';
	}
	if (!Core_isAdmin()) {
		echo '<br/><br/><br/><p style="font-size:small">if you are the retailer, '
			.'please <a href="/ww.admin/">log in</a>, then scan the QR code again.';
	}
	else {
		echo '<br/><br/><br/><a href="/a/p=online-store/f=adminRedeemVoucher/'
			.'oid='.$oid.'/pid='.$pid.'">Mark this voucher as redeemed.</a>';
	}
	exit;
}

// }
// { OnlineStore_checkVoucher

/**
	* check a voucher to see if it's valid
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_checkVoucher($params) {
	require_once dirname(__FILE__).'/frontend/voucher-libs.php';
	$valid=OnlineStore_voucherCheckValidity($params['code'], $params['email']);
	if ($valid['error']) {
		return $valid;
	}
	else {
		return array('ok'=>1);
	}
}

// }
// { OnlineStore_getCountries

/**
	* get list of countries selected for the checkout
	*
	* @return array of countries
	*/
function OnlineStore_getCountries() {
	$page_id=(int)$_REQUEST['page_id'];
	$countries=json_decode(dbOne('select value from page_vars where page_id='.$page_id.' and name="online-store-countries"', 'value'));
	$c=array();
	foreach ($countries as $k=>$v) {
		$c[]=$k;
	}
	return $c;
}

// }
// { OnlineStore_getQrCode

/**
	* output a QR code for a voucher
	*
	* @return null
	*/
function OnlineStore_getQrCode() {
	require_once dirname(__FILE__).'/../products/phpqrcode.php';
	$url=base64_decode($_REQUEST['b64']);
	$fname=USERBASE.'/ww.cache/online-store/qr'.md5($url);
	if (!file_exists($fname)) {
		@mkdir(USERBASE.'/ww.cache/online-store');
		QRcode::png(
			$url,
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
	exit;
}

// }
// { OnlineStore_listSavedLists

/**
	* shopping lists
	*
	* @param array $params parameters
	*
	* @return array shopping list names
	*/
function OnlineStore_listSavedLists($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	$names=array();
	$rs=dbAll(
		'select name from online_store_lists where user_id='
		.$_SESSION['userdata']['id'].' order by name'
	);
	foreach ($rs as $r) {
		$names[]=$r['name'];
	}
	return array('names'=>$names);
}

// }
// { OnlineStore_loadSavedList

/**
	* save a shopping list
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_loadSavedList($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	if (!@$params['name']) {
		return array('error'=>'no list name supplied');
	}
	
	$data=dbOne(
		'select details from online_store_lists where '
		.' name="'.addslashes($params['name']).'" and user_id='
		.$_SESSION['userdata']['id'], 'details'
	);
	if (!$data) {
		return array('error'=>'no such list exists');
	}
	$_SESSION['online-store']=json_decode($data, true);
	
	return array('success'=>1);
}

// }
// { OnlineStore_pandpGetList

/**
	* get list of post and packaging methods
	*
	* @return array of pandp methods
	*/
function OnlineStore_pandpGetList() {
	$page_id=(int)$_REQUEST['page_id'];
	$pandp=json_decode(dbOne(
		'select value from page_vars where page_id='.$page_id
		.' and name="online_stores_postage"',
		'value'
	));
	$c=array();
	foreach ($pandp as $k=>$v) {
		$c[]=$v->name;
	}
	return $c;
}

// }
// { OnlineStore_saveSavedList

/**
	* save a shopping list
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_saveSavedList($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	if (!@$params['name']) {
		return array('error'=>'no list name supplied');
	}
	
	$data=json_encode($_SESSION['online-store']);
	dbQuery(
		'delete from online_store_lists where name="'.addslashes($params['name'])
		.'" and user_id='.$_SESSION['userdata']['id']
	);
	dbQuery(
		'insert into online_store_lists set name="'.addslashes($params['name'])
		.'",user_id='.$_SESSION['userdata']['id'].',details="'
		.addslashes($data).'"'
	);
	return array('success'=>1);
}

// }
