<?php
/**
	* generates a formatted price, including currency symbol
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/


/**
  * generates a formatted price, including currency symbol
  *
  * @param array  $params parameters for the function
	* @param object $smarty the current Smarty object
  *
  * @return string HTML of the price
  */
function OnlineStore_productPriceFull2($params, $smarty) {
	$pid=$smarty->_tpl_vars['product']->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		$product->vals['online-store']=array(
			'_price'=>0,
			'_trade_price'=>0,
			'_sale_price'=>0,
			'_bulk_price'=>0,
			'_bulk_amount'=>0,
			'_weight'=>0,
			'_vatfree'=>0,
			'_custom_vat_amount'=>0
		);
	}
	$p=$product->vals['online-store'];
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	foreach ($p as $k=>$v) {
		$p[$k]=(float)$v;
	}
	if ($p['_sale_price']) {
		$tmp='<strike class="os_price">'.OnlineStore_numToPrice($p['_price']*$vat)
			.'</strike> <strong class="os_price">'
			.OnlineStore_numToPrice($p['_sale_price']*$vat).'</strong>';
	}
	else {
		$tmp='<strong class="os_price">'
			.OnlineStore_numToPrice($p['_price']*$vat).'</strong>';
	}
	if ($p['_bulk_price'] && $p['_bulk_amount']) {
		$tmp.='<br />'.OnlineStore_numToPrice($p['_bulk_price']*$vat).' for '
			.$p['_bulk_amount'].' or more';
	}
	$tmp='<span class="os_full_price">'.$tmp.'</span>';
	return $tmp;
}
