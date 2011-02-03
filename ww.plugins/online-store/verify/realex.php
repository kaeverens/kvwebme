<?php
/**
	* based on information in the Real Auth Developers Guide
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
*/

$account=   $_REQUEST['ACCOUNT'];
$authcode=  $_REQUEST['AUTHCODE'];
$batchid=   $_REQUEST['BATCHID'];
$id=        $_REQUEST['ORDER_ID'];
$merchantid=$_REQUEST['MERCHANT_ID'];
$message=   $_REQUEST['MESSAGE'];
$pasref=    $_REQUEST['PASREF'];
$result=    $_REQUEST['RESULT'];
$sha1hash=  $_REQUEST['SHA1HASH'];
$timestamp= $_REQUEST['TIMESTAMP'];

// { check that SHA1 matches what we expect
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
// TIMESTAMP.MERCHANT_ID.ORDER_ID.RESULT.MESSAGE.PASREF.AUTHCODE
$sha1=sha1($timestamp.'.'.$merchantid.'.'.$id.'.'.$result.'.'.$message.'.'.$pasref.'.'.$authcode);
$p=Page::getInstanceByType('online-store');
if(!$p){
	die('no online store detected on this site');
}
$p->initValues();
if(
	!isset($p->vars['online_stores_realex_sharedsecret'])
	|| !($p->vars['online_stores_realex_sharedsecret'])
){
	die('Realex not configured on this server');
}
$sha1=sha1($sha1.'.'.$p->vars['online_stores_realex_sharedsecret']);
if($sha1!=$sha1hash){
	die('SHA1 hash does not match. Received '.$sha1hash.', expected '.$sha1);
}
// }
// { check that purchase was successful
if($result!='00'){
	echo '<p>Error '.$result.'. "'.htmlspecialchars($message).'"</p>';
	echo '<p>If you think this is a problem on our site, please contact us with the above error message, and the order ID '.$id.'</p>';
	exit;
}
// }
// process payment
$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
require dirname(__FILE__).'/process-order.php';
OnlineStore_processOrder($id, $order);
$rid=$p->vars['online_store_redirect_to'];
$url='http://'.$_SERVER['HTTP_HOST'].'/';
if($rid){
	$rp=Page::getInstance($rid);
	if($rp){
		$url.=$rp->getRelativeUrl();
	}
}
echo '<script>document.location="'.addslashes($url).'?total='.$order['total'].'";</script>'
	.'<p>Thank you!</p>'
	.'<p>Please <a href="'.htmlspecialchars($url).'">click here</a> to continue.</p>';
