<?php
/**
	* this ajax file sets how many of a specified product is in the basket
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/
session_start();
$md5=$_REQUEST['md5'];
if (!isset($_SESSION['online-store']['items'][$md5])) {
 die('no such item');
}

$amt=(int)$_REQUEST['amt'];
if ($amt<1) {
	unset($_SESSION['online-store']['items'][$md5]);
}
else {
	$_SESSION['online-store']['items'][$md5]['amt']=$amt;
}

require '../libs.php';
$total=OnlineStore_calculateTotal();
$item_total=$amt?$_SESSION['online-store']['items'][$md5]['cost']*$amt:0;

echo '{'.
	'"md5": "'.$md5.'",'.
	'"amt":'.$amt.','
	.'"item_total":'.$item_total.','
	.'"total":'.$total
.'}';
