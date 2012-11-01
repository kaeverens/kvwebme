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

// { Products_amountInStock2

/**
	* get amount of product in stock (simple)
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return int number in stock
	*/
function Products_amountInStock2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	return (int)$product->vals['stockcontrol_total'];
}

// }
// { Products_map2

/**
	* get a map centered on the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return html of the map
	*/
function Products_map2($params, $smarty) {
	$params=array_merge(
		array(
			'width'=>160,
			'height'=>'120'
		),
		$params
	);
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	$uid=(int)$product->vals['user_id'];
	if (!$uid) {
		return 'unknown location';
	}
	$user=User::getInstance($uid, false, false);
	if (!$user) {
		return 'unknown user';
	}
	$lat=(float)$user->get('location_lat');
	$lng=(float)$user->get('location_lng');
	WW_addScript('products/j/maps.js');
	return '<div class="products-map"'
		.' data-lat="'.$lat.'"'
		.' data-lng="'.$lng.'"'
		.' data-pid="'.$pid.'"'
		.' style="width:'.((int)$params['width']).'px;'
		.'height:'.((int)$params['height']).'px"></div>';
}

// }
// { Products_owner2

/**
	* return data about the product owner
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string owner variables
	*/
function Products_owner2($params, $smarty) {
	$params=array_merge(
		array(
			'field'=>'name',
		),
		$params
	);
	// { set up user object
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	$uid=(int)$product->vals['user_id'];
	if (!$uid) {
		return 'unknown';
	}
	$user=User::getInstance($uid, false, false);
	if (!$user) {
		return 'unknown';
	}
	// }
	return $user->get($params['field']);
}

// }
// { Products_priceBase2

/**
	* return the base price for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string the price
	*/
function Products_priceBase2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	if ((!isset($params['vat']) || !$params['vat'])
		&& $_SESSION['onlinestore_prices_shown_post_vat']
	) {
		$params['vat']=1;
	}
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice(
		$product->getPriceBase()*$vat, true, (int)@$params['round']
	);
}

// }
// { Products_priceBulk2

/**
	* return the bulk price for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string the price
	*/
function Products_priceBulk2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	if (!$params['vat'] && $_SESSION['onlinestore_prices_shown_post_vat']) {
		$params['vat']=1;
	}
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$p=$product->vals['online-store'];
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	$price=$p['_bulk_price']?$p['_bulk_price']:$product->getPriceBase();
	return OnlineStore_numToPrice($price*$vat, true, (int)@$params['round']);
}

// }
// { Products_priceDiscount2

/**
	* show the difference between base and sale price
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceDiscount2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	if (!$params['vat'] && $_SESSION['onlinestore_prices_shown_post_vat']) {
		$params['vat']=1;
	}
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$discount=$product->getPriceBase()-$product->getPrice('sale');
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice($discount*$vat, true, (int)@$params['round']);
}

// }
// { Products_priceDiscountPercent2

/**
	* show the percentage of the discount
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceDiscountPercent2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	if ($product->getPrice()) {
		$p=$product->getPrice();
		return (int)(100* (($p-$product->getPrice('sale'))/$p));
	}
	return '--';
}

// }
// { Products_priceSale2

/**
	* return the sale price for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_priceSale2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	if ((!isset($params['vat']) || !$params['vat'])
		&& $_SESSION['onlinestore_prices_shown_post_vat']
	) {
		$params['vat']=1;
	}
	$product=Product::getInstance($pid);
	if (!isset($product->vals['online-store'])) {
		return '0';
	}
	$vat=isset($params['vat']) && $params['vat']
		?(100+$_SESSION['onlinestore_vat_percent'])/100
		:1;
	return OnlineStore_numToPrice(
		$product->getPrice('sale')*$vat,
		true,
		(int)@$params['round']
	);
}

// }
// { Products_qrCode2

/**
	* return a QR code for the product
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string image
	*/
function Products_qrCode2($params, $smarty) {
	if (@$smarty->smarty->tpl_vars['isvoucher']->value!=1) {
		return '<img src="/a/p=products/f=showQrCode/pid='
			.$smarty->smarty->tpl_vars['product']->value->id.'"/>';
	}
	return '';
}

// }
// { Products_soldAmount2

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
			'none'=>'0',
			'one'=>'1',
			'many'=>'%d'
		),
		$params
	);
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
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

// }
// { Products_user2

/**
	* show owner's name
	*
	* @param array  $params parameters
	* @param object $smarty Smarty object
	*
	* @return string HTML
	*/
function Products_user2($params, $smarty) {
	$pid=$smarty->smarty->tpl_vars['product']->value->id;
	$product=Product::getInstance($pid);
	$uid=(int)$product->get($params['pfield']);
	$user=User::getInstance($uid, false, false);
	if ($user) {
		return $user->get($params['ufield']);
	}
}

// }
