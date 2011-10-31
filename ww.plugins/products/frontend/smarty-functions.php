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