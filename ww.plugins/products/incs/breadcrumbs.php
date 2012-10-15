<?php
/**
	* Products_breadcrumbs
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $PAGEDATA;
$breadcrumbs='';
Products_frontendVarsSetup($PAGEDATA);
if (isset($_REQUEST['product_cid']) && $_REQUEST['product_cid']) {
	$c=ProductCategory::getInstance($_REQUEST['product_cid']);
	$breadcrumbs.=' &raquo; <a class="product-category" href="'
		.$c->getRelativeUrl().'">'.htmlspecialchars($c->vals['name']).'</a>';
}
if (isset($_REQUEST['product_id']) && $_REQUEST['product_id']) {
	$c=Product::getInstance($_REQUEST['product_id'], false, 1);
	$breadcrumbs.=' &raquo; <a class="product-product" href="'
		.$c->getRelativeUrl().'">'.htmlspecialchars($c->get('_name')).'</a>';
}
