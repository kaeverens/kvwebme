<?php
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
function Products_priceDiscount2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	$discount=$p['_price']-$p['_sale_price'];
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($discount*$vat);
}
function Products_priceDiscountPercent2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	return (int)(100*$p['_sale_price']/$p['_price']).'%';
}
function Products_priceSale2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($p['_sale_price']*$vat);
}
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
function Products_qrCode2($params, $smarty) {
#	require_once dirname(__FILE__).'/../phpqrcode.php';
	if (@$emarty->_tpl_vars['isvoucher']==1) {
	}
	else {
		return '<img src="/a/p=products/f=showQrCode/pid='
			.$smarty->_tpl_vars['product']->id.'"/>';
	}
	return 'test';
}
