<?php
/**
	* send bulk SMS messages
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}
require_once SCRIPTBASE.'ww.plugins/sms/admin/libs.php';

$aid=(int)$_REQUEST['to'];
$msg=$_REQUEST['msg'];
if (!$msg || preg_replace('/a-zA-Z0-9 !_\-.,:\'"/', '', $msg)!=$msg) {
	exit;
}

$tos=array();
$to_names=array();
$subs=dbOne(
	'select subscribers from sms_addressbooks where id='.$aid.' limit 1',
	'subscribers'
);
$subs=dbAll(
	'select name,phone from sms_subscribers where id in ('
	.preg_replace('/[^0-9,]/', '', $subs).')'
);
foreach ($subs as $sub) {
	$tos[]=$sub['phone'];
	$to_names[]=preg_replace('/[^a-zA-Z0-9 \-.\']/', '', $sub['name']);
}

$ret=SMS_callApi(
	'send-bulk',
	'&to='.join(',', $tos).'&message='.urlencode($msg).'&names='
	.join(',', $to_names)
);
echo json_encode($ret);
