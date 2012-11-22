<?php
/**
	* addToCart
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

$id=(int)$_REQUEST['product_id'];
require_once dirname(__FILE__).'/show.php';
$product=Product::getInstance($id);
if (!$product) {
	return;
}
$amount=1;
if (isset($_REQUEST['products-howmany'])) {
	$amount=(int)$_REQUEST['products-howmany'];
}
// { find "custom" values
$price_amendments=0;
$vals=array();
$md5='';
$product_type=ProductType::getInstance($product->vals['product_type_id']);
$long_desc='';
foreach ($_REQUEST as $k=>$v) {
	if (strpos($k, 'products_values_')===0) {
		$n=str_replace('products_values_', '', $k);
		$data_field=$product_type->getField($n);
		if ($data_field === false // not a real field
			|| $data_field->u!=1    // not a user-choosable field
		) {
			continue;
		}
		switch ($data_field->t) {
			case 'selectbox': // {
				$ok=0;
				if (@$product->vals[$n]) { // if product has custom values
					$strs=explode("\n", $product->vals[$n]);
					foreach ($strs as $a=>$b) {
						$strs[$a]=trim($b);
					}
				}
				else { // else use the product type defaults
					$strs=explode("\n", $data_field->e);
				}
				if (in_array($v, $strs)) {
					if (strpos($v, '|')!==false) {
						$bits=explode('|', $v);
						$price_amendments+=(float)$bits[1];
					}
					$ok=1;
				}
				if (!$ok) {
					continue;
				}
			break; // }
			case 'selected-image': // {
				$v='http://'.$_SERVER['HTTP_HOST'].'/kfmget/'.$v;
				$long_desc='<img style="float:left" src="'.$v.',width=60,height=60"/>';
			break; // }
		}
		$vals[]='<div class="products-desc-'
			.preg_replace('/[^a-zA-Z0-9]/', '', $k).'">'
			.'<span class="__">'.$n.'</span>: '.$v.'</div>';
	}
}
if (count($vals)) {
	$long_desc.=join("\n", $vals).'<br style="clear:left"/>';
	$md5=','.md5($long_desc.'products_'.$id);
}
// }
list($price, $amount, $vat)=Products_getProductPrice(
	$product, $amount, $md5
);
if (isset($_REQUEST['products_values__custom-price'])
	&& (float)$_REQUEST['products_values__custom-price']
	&& $product_type->has_userdefined_price
) {
	$price=(float)$_REQUEST['products_values__custom-price'];
}
// { does the amount requested bring it over the maximum allowed per purchase
$max_allowed=isset($product->vals['online-store']['_max_allowed'])
	?(int)$product->vals['online-store']['_max_allowed']
	:0;
// }
OnlineStore_addToCart(
	$price+$price_amendments,
	$amount,
	__FromJson($product->get('name')),
	$long_desc,
	'products_'.$id.$md5,
	$_SERVER['HTTP_REFERER'],
	$vat,
	$id,
	(int)(@$product->vals['online-store']['_deliver_free']),
	(int)(@$product->vals['online-store']['_not_discountable']),
	$max_allowed,
	$product->stock_number
);
