<?php
/**
	* not sure yet
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

$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
if ($req=='cmd=_notify-validate') {
	die('please don\'t access this file directly');
}
// post back to PayPal system to validate
$header  = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Host: www.sandbox.paypal.com\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

$content='';
if (!$fp) {
	// HTTP ERROR
}
else {
	fputs($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets($fp, 1024);
		$content.=$res;
		
		if (strcmp($res, "VERIFIED") == 0) {
			$paid=$_POST['mc_gross']-$_POST['mc_fee'];
			if ($paid<0) {
				Core_quit();
			} 
			$extras = dbOne(
				'select extras from user_accounts'
				.' where id='.((int)$_REQUEST['custom']),
				'extras'
			);
			$extras=json_decode($extras, true);
			$extras['paid_credits'] += (int)$_REQUEST['item_number'];
			dbQuery(
				'update user_accounts set'
				.' extras="'.addslashes(json_encode($extras)).'"'
				.' where id='.$_REQUEST['custom']
			);
		}
	}
	fclose($fp);
}
