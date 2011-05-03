<?php
/**
	* partly from https://www.paypaltech.com/SG2/
	* read the post from PayPal system and add 'cmd'
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
*/
$req = 'cmd=_notify-validate';
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}
if ($_POST['payment_status'] == 'Refunded') {
	exit;
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
			require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
			$id=(int)$_POST['item_number'];
			if ($id<1) {
				exit;
			}

			// check that payment_amount/payment_currency are correct
			$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
			if ($order['total'] != $_POST['mc_gross']) {
				$str='';
				foreach ($_POST as $key => $value) {
					$str.=$key." = ". $value."\n";
				}
				mail(
					'kae@verens.com', 
					$_SERVER['HTTP_HOST'].' paypal hack'
					, $str
				);
				exit;
			}

			// process payment
			require dirname(__FILE__).'/process-order.php';
			OnlineStore_processOrder($id, $order);
		}
		else if (strcmp($res, "INVALID") == 0) {
		}
		
	}
	fclose($fp);
}
