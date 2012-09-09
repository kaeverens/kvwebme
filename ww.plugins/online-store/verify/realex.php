<?php
/**
	* based on information in the Real Auth Developers Guide
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
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
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
// TIMESTAMP.MERCHANT_ID.ORDER_ID.RESULT.MESSAGE.PASREF.AUTHCODE
$sha1=sha1(
	$timestamp.'.'.$merchantid.'.'.$id.'.'.$result.'.'.$message.'.'.$pasref
	.'.'.$authcode
);
$p=Page::getInstanceByType('online-store');
if (!$p) {
	die(__('No online store detected on this site'));
}
$p->initValues();
if (!isset($p->vars['online_stores_realex_sharedsecret'])
	|| !($p->vars['online_stores_realex_sharedsecret'])
) {
	die(__('Realex not configured on this server'));
}
$sha1=sha1($sha1.'.'.$p->vars['online_stores_realex_sharedsecret']);
if ($sha1!=$sha1hash) {
	die(
		__(
			'SHA1 hash does not match. Received %1, expected %2',
			array($sha1hash, $sha1),
			'core'
		)
	);
}
// }
// { check that purchase was successful
if ($result!='00') {
	echo '<p>'.__('Error %1, "%2"', array($result, $message), 'core').'</p>'
		.'<p>'.__(
			'If you think this is a problem on our site, please contact us with'
			.' the above error message, and the order ID %1',
			array($id),
			'core'
		)
		.'</p>';
	Core_quit();
}
// }
// { process payment
$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
require dirname(__FILE__).'/../order-status.php';
OnlineStore_processOrder($id, $order);
// }
$rid=$p->vars['online_store_redirect_to'];
$url='http://'.$_SERVER['HTTP_HOST'].'/';
$pfound=false;
if ($rid) {
	$rp=Page::getInstance($rid);
	if ($rp) {
		$pfound=true;
		$url.=$rp->getRelativeUrl().'?total='.$order['total'];
	}
}
if (!$pfound) {
	$rp=Page::getInstanceByType('privacy');
	if ($rp) {
		$pfound=true;
		$url.=$rp->getRelativeUrl().'?onlinestore_iid='.$id;
	}
}
echo '<script>document.location="'.addslashes($url).'";</script>'
	.'<p>'.__('Thank you').'</p>'
	.'<p>'.__(
		'Please <a href="%1">click here</a> to continue',
		array($url), 'core'
	)
	.'.</p>';
