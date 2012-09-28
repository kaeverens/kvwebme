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

// { OnlineStore_productPriceFull2

/**
  * generates a formatted price, including currency symbol
  *
  * @param array  $params parameters for the function
	* @param object $smarty the current Smarty object
  *
  * @return string HTML of the price
  */
function OnlineStore_productPriceFull2($params, $smarty) {
	$params=array_merge(
		array(
			'vat'=>0
		),
		$params
	);
	if (!$params['vat'] && $_SESSION['onlinestore_prices_shown_post_vat']) {
		$params['vat']=1;
	}
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		$product->vals['online-store']=array(
			'_price'=>0,
			'_trade_price'=>0,
			'_sale_price'=>0,
			'_sale_price_type'=>0,
			'_bulk_price'=>0,
			'_bulk_amount'=>0,
			'_weight'=>0,
			'_vatfree'=>0,
			'_custom_vat_amount'=>0
		);
	}
	$p=$product->vals['online-store'];
	$vat=$params['vat']?(100+$_SESSION['onlinestore_vat_percent'])/100:1;
	$vatclass=$params['vat']?' vat':'';
	foreach ($p as $k=>$v) {
		$p[$k]=(float)$v;
	}
	if ($p['_sale_price']) {
		$tmp='<strike class="os_price">'.OnlineStore_numToPrice($product->getPrice()*$vat)
			.'</strike> <strong class="os_price with-sale-price'.$vatclass.'">'
			.OnlineStore_numToPrice($product->getPrice('sale')*$vat).'</strong>';
	}
	else {
		$tmp='<strong class="os_price'.$vatclass.'">'
			.OnlineStore_numToPrice($p['_price']*$vat).'</strong>';
	}
	if ($p['_bulk_price'] && $p['_bulk_amount']) {
		$tmp.='<br />'.OnlineStore_numToPrice($p['_bulk_price']*$vat).' for '
			.$p['_bulk_amount'].' or more';
	}
	$tmp='<span class="os_full_price">'.$tmp.'</span>';
	return $tmp;
}

// }
