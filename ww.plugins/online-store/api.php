<?php
/**
	* check a voucher to see if it's valid
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
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
