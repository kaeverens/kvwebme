<?php
/**
	* smarty functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* return the base price for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string the price
	*/
function Products_priceBase2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($p['_price']*$vat);
}

/**
	* show the difference between base and sale price
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceDiscount2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	$discount=$p['_price']-$product->getPrice('sale');
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($discount*$vat);
}

/**
	* show the percentage of the discount
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceDiscountPercent2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	return (int)(100*$product->getPrice('sale')/$product->getPrice()).'%';
}

/**
	* return the sale price for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceSale2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($product->getPrice('sale')*$vat);
}

/**
	* show how many have been sold
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_soldAmount2($params, $smarty) {
	$params=array_merge(
		array(
			'none'=>'none sold',
			'one'=>'one sold',
			'many'=>'%d sold'
		),
		$params
	);
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '';
	}
	$sold=(int)$product->vals['online-store']['_sold_amt'];
	if ($sold==0) {
		return __($params['none']);
	}
	if ($sold==1) {
		return __($params['one']);
	}
	return str_replace('%d', $sold, $params['many']);
}

/**
	* return a QR code for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string image
	*/
function Products_qrCode2($params, $smarty) {
	if (@$smarty->_tpl_vars['isvoucher']!=1) {
		return '<img src="/a/p=products/f=showQrCode/pid='
			.$smarty->_tpl_vars['product']->id.'"/>';
	}
	return 'test';
}
