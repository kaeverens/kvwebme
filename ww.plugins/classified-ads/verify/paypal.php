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
mail('kae.verens@gmail.com', 'test', 'failed');
	// HTTP ERROR
}
else {
mail('kae.verens@gmail.com', 'test4', print_r($_REQUEST, true));
	fputs($fp, $header . $req);
	while (!feof($fp)) {
		$res = fgets($fp, 1024);
		if (strcmp($res, "VERIFIED") == 0) {
			require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
			$id=(int)$_POST['item_number'];
			if ($id<1) {
				exit;
			}
			// create ad
			$data=dbRow('select * from classifiedads_purchase_orders where id='.$id);
			mail('kae.verens@gmail.com', 'test', print_r($data, true));
			$sql='insert into classifiedads_ad set user_id='.$data['user_id']
				.',email="'.addslashes($data['email']).'",creation_date=now()'
				.',title="'.addslashes($data['target_type']).'"'
				.',body="'.addslashes($data['description']).'"'
				.',expiry_date=date_add(now(), interval '.$data['days'].' day)'
				.', status=1, category_id='.$data['category_id'];
			mail('kae.verens@gmail.com', 'test2', $sql);
			dbQuery($sql);
			$ad_id=dbLastInsertId();
		}
		else if (strcmp($res, "INVALID") == 0) {
		}
	}
	fclose($fp);
}
