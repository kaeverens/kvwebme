<?php
/**
	* partly from https://www.paypaltech.com/SG2/
	* read the post from PayPal system and add 'cmd'
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
if ($_POST['payment_status'] == 'Refunded') {
	Core_quit();
}
if ($req=='cmd=_notify-validate') {
	// TODO: translation needed
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
			$id=(int)$_POST['item_number'];
			if ($id<1) {
				Core_quit();
			}

			// check that payment_amount/payment_currency are correct
			$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
			if (round($order['total']) != round($_POST['mc_gross'])) {
				$str='';
				foreach ($_POST as $key => $value) {
					$str.=$key." = ". $value."\n";
				}
				// TODO: you should be able to edit the email address here - e.g. test domains will have a strange email address
				$eml='info@'.preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
				Core_mail(
					$eml,
					$_SERVER['HTTP_HOST'].' paypal hack',
					$str,
					$eml
				);
				Core_quit();
			}

			// process payment
			require dirname(__FILE__).'/../order-status.php';
			OnlineStore_processOrder($id, $order);
		}
		else if (strcmp($res, "INVALID") == 0) {
		}
		
	}
	fclose($fp);
}
