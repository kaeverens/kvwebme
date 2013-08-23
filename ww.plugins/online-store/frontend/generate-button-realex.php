<?php
/**
	* generate a button for Realex payments
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $DBVARS;
$timestamp=date('YmdHis');
$total=ceil(100*$total);
$sha1hash=sha1(
	$timestamp
	.'.'.$PAGEDATA->vars['online_stores_realex_merchantid']
	.'.'.$id
	.'.'.$total
	.'.'.$DBVARS['online_store_currency']
);
$sha1hash=sha1(
	$sha1hash.'.'.$PAGEDATA->vars['online_stores_realex_sharedsecret']
);
$html='<form id="online-store-realex" method="post" action="'
	.'https://epage.payandshop.com/epage.cgi">'
	.'<input type="hidden" value="'
	.$PAGEDATA->vars['online_stores_realex_merchantid']
	.'" name="MERCHANT_ID" />'
	.'<input type="hidden" value="'.$id.'" name="ORDER_ID" />'
	.'<input type="hidden" value="internet" name="ACCOUNT" />'
	.'<input type="hidden" value="'.$total.'" name="AMOUNT" />'
	.'<input type="hidden" value="'.$DBVARS['online_store_currency']
	.'" name="CURRENCY" />'
	.'<input type="hidden" value="'.$timestamp.'" name="TIMESTAMP" />'
	.'<input type="hidden" value="'.$sha1hash.'" name="SHA1HASH" />'
	.'<input type="hidden" value="1" name="AUTO_SETTLE_FLAG" />'
	.'<input type="hidden" value="'
	.addslashes(__('Purchase made from %1', array($_SERVER['HTTP_HOST']), 'core'))
	.'" name="COMMENT1"/>'
	.'<input type="submit" value="'
	.addslashes(__('Proceed to Payment')).'" /></form>'
	.'<script defer="defer">$("#online-store-realex").submit()</script>';
