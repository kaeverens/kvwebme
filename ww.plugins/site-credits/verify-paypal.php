<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once dirname(__FILE__).'/libs.php';

$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
if ($req=='cmd=_notify-validate') {
	die('please don\'t access this file directly');
}
// post back to PayPal system to validate
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
if (!$fp) {
	// HTTP ERROR
} else {
	fputs($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets($fp, 1024);
		if (strcmp($res, "VERIFIED") == 0) {
			$paid=$_POST['mc_gross']-$_POST['mc_fee'];
			if ($paid<0) {
				Core_quit();
			}
			$GLOBALS['DBVARS']['sitecredits-credits']
				=((float)$GLOBALS['DBVARS']['sitecredits-credits'])+$paid;
			Core_configRewrite();
			SiteCredits_recordTransaction('credits purchased', $paid);
		}
	}
	fclose($fp);
}
