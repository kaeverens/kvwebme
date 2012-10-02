<?php
/**
	* verify a QuickPay payment
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

$md5secret=dbOne(
	'select value from page_vars,pages where page_id=pages.id and '
	.'pages.type="online-store" and '
	.'page_vars.name="online_stores_quickpay_secret"',
	'value'
);
if (!$md5secret) { // no md5 secret entered
	Core_quit();
}
// { calculate expected MD5
$expected_md5 = md5(
	$_REQUEST['msgtype']
	.$_REQUEST['ordernumber']
	.$_REQUEST['amount']
	.$_REQUEST['currency']
	.$_REQUEST['time']
	.$_REQUEST['state']
	.$_REQUEST['qpstat']
	.$_REQUEST['qpstatmsg']
	.$_REQUEST['chstat']
	.$_REQUEST['chstatmsg']
	.$_REQUEST['merchant']
	.$_REQUEST['merchantemail']
	.$_REQUEST['transaction']
	.$_REQUEST['cardtype']
	.$_REQUEST['cardnumber']
	.$_REQUEST['splitpayment']
	.$_REQUEST['fraudprobability']
	.$_REQUEST['fraudremarks']
	.$_REQUEST['fraudreport']
	.$_REQUEST['fee']
	.$md5secret
);
// }
if (strtolower($expected_md5) == strtolower($_REQUEST['md5check'])) {
	$id=(int)preg_replace('/^0*/', '', $_REQUEST['ordernumber']);
	dbQuery(
		'update online_store_orders set status=4'
		.', meta="'.addslashes(json_encode($_REQUEST)).'"'
		.', authorised=1 where id='.$id
	);
	require_once dirname(__FILE__).'/../order-status.php';
	OnlineStore_sendInvoiceEmail($id, $order);
	OnlineStore_exportToFile($id, $order);
}
