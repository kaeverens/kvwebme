<?php
/**
	* definition file for Online-Store plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'Online Store',
	'admin' => array(
		'page_type' => 'OnlineStore_adminPageForm',
		'menu' => array(
			'Site Options>Online Store' => 'site-options',
		),
		'widget' => array(
			'form_url' => '/ww.plugins/online-store/admin/widget-form.php'
		)
	),
	'description' => 'Add online-shopping capabilities to a number of other plugins.',
	'frontend' => array(
		'widget' => 'OnlineStore_showBasketWidget',
		'page_type' => 'OnlineStore_frontend',
		'template_functions' => array(
			'ONLINESTORE_PAYMENT_TYPES' => array(
				'function' => 'OnlineStore_payment_types'
			),
			'PRODUCTS_FULL_PRICE' => array(
				'function' => 'OnlineStore_productPriceFull'
			)
		)
	),
	'triggers' => array(
		'displaying-pagedata'      => 'OnlineStore_pagedata',
		'initialisation-completed' => 'OnlineStore_startup'
	),
	'version' => '7'
);
// }
// { currency symbols
$online_store_currencies=array(
	'EUR'=>array('&euro;','Euro'),
	'GBP'=>array('&pound;','Pound Sterling')
);
// }

/**
	* adds a product to the cart
	*
	* @param float  $cost       cost of the product
	* @param int    $amt        how many to add
	* @param string $short_desc short description of the product
	* @param string $long_desc  long description of the product
	* @param string $md5        a unique key for storing this product in the session
	* @param string $url        URL where the product can be viewed
	*
	* @return null
	*/
function OnlineStore_addToCart(
	$cost=0, $amt=0, $short_desc='',
	$long_desc='', $md5='', $url='', $vat=true
) {
	// { add item to session
	if (!isset($_SESSION['online-store'])) {
		$_SESSION['online-store']=array('items'=>array(),'total'=>0);
	}
	$item=(isset($_SESSION['online-store']['items'][$md5]))
		?$_SESSION['online-store']['items'][$md5]
		:array('cost'=>0,'amt'=>0,'short_desc'=>$short_desc,
			'long_desc'=>$long_desc,'url'=>$url);
	$item['cost']=$cost;
	$item['amt']+=$amt;
	$item['short_desc']=$short_desc;
	$item['url']=$url;
	$item['vat']=$vat;
	$_SESSION['online-store']['items'][$md5]=$item;
	// }
	require dirname(__FILE__).'/libs.php';
	OnlineStore_calculateTotal();
}

/**
	* admin area Page form
	*
	* @param object $page Page array from database
	* @param array  $vars Page's custom variables
	*
	* @return string
	*/
function OnlineStore_adminPageForm($page, $vars) {
	require dirname(__FILE__).'/admin/index.php';
	return $c;
}

/**
	* stub function to load frontend page-type
	*
	* @param object $PAGEDATA the current page
	*
	* @return string
	*/
function OnlineStore_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/index.php';
	return $c;
}

/**
	* return HTML for a PayPal button to pay for the current Online-Store order
	*
	* @param object $PAGEDATA the checkout page
	* @param int    $id       the order ID
	* @param float  $total    the order total
	* @param string $return   URL that the buyer should be returned to after a purchase
	*
	* @return string
	*/
function OnlineStore_generatePaypalButton($PAGEDATA, $id, $total, $return='') {
	global $DBVARS;
	$total=sprintf("%.2f",$total);
	return '<form id="online-store-paypal" method="post" action="https://www.paypal.com'
		.'/cgi-bin/webscr"><input type="hidden" value="_xclick" name="cmd"/>'
		.'<input type="hidden" value="'.$PAGEDATA->vars['online_stores_paypal_address']
		.'" name="business"/>'
		.'<input type="hidden" value="Purchase made from '.$_SERVER['HTTP_HOST']
		.'" name="item_name"/>'
		.'<input type="hidden" value="'.$id.'" name="item_number"/>'
		.'<input type="hidden" value="'.$total.'" name="amount"/>'
		.'<input type="hidden" value="'.$DBVARS['online_store_currency']
		.'" name="currency_code"/><input type="hidden" value="1" name="no_shipping"/>'
		.'<input type="hidden" value="1" name="no_note"/>'
		.'<input type="hidden" name="return" value="'.htmlspecialchars($return).'" />'
		.'<input type="hidden" value="http://'.$_SERVER['HTTP_HOST']
		.'/ww.plugins/online-store/verify/paypal.php" name="notify_url"/>'
		.'<input type="hidden" value="IC_Sample" name="bn"/><input type="image" alt="Make'
		.' payments with payPal - it\'s fast, free and secure!" name="submit" src="https:'
		.'//www.paypal.com/en_US/i/btn/x-click-but23.gif"/><img width="1" height="1" src='
		.'"https://www.paypal.com/en_US/i/scr/pixel.gif" alt=""/></form>';
}

/**
	* return HTML for a Realex button to pay for the current Online-Store order
	*
	* @param object $PAGEDATA the checkout page
	* @param int    $id       the order ID
	* @param float  $total    the order total
	* @param string $return   URL that the buyer should be returned to after a purchase
	*
	* @return string
	*/
function OnlineStore_generateRealexButton($PAGEDATA, $id, $total, $return='') {
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
		$sha1hash
		.'.'.$PAGEDATA->vars['online_stores_realex_sharedsecret']
	);
	return '<form id="online-store-realex" method="post" action="'
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
		.'<input type="hidden" value="Purchase made from '.$_SERVER['HTTP_HOST']
		.'" name="COMMENT1"/>'
		.'<input type="submit" value="Proceed to Payment" /></form>'
		.'<script>$("#online-store-realex").submit()</script>';
}

/**
	* returns currency information to be added to global JS script
	*
	* @return string
	*/
function OnlineStore_pagedata() {
	return ',"currency":"'.$_SESSION['currency']['symbol'].'"';
}

/**
	* returns a selectbox with payment types (PayPal, Realex, etc) in it.
	*
	* @return string
	*/
function OnlineStore_payment_types() {
	require dirname(__FILE__).'/frontend/payment-types.php';
	return $c;
}

/**
	* Smarty function for returning a product's price, including currency symbol
	*
	* @return string
	*/
function OnlineStore_productPriceFull($params, &$smarty) {
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


/**
	* when given a number, it returns that number formatted to a currency
	*
	* @return string
	*/
function OnlineStore_numToPrice($val, $sym=true, $rounded=false) {
	$rate=$_SESSION['currency']['value'];
	$sym=$_SESSION['currency']['symbol'];
	return $rounded
		?$sym.round($val*$rate)
		:$sym.sprintf("%.2f",$val*$rate);
}

/**
	* returns a HTML string to show the Online-Store basket
	*
	* @return string
	*/
function OnlineStore_showBasketWidget($vars=null) {
	global $DBVARS;
	$html='<div class="online-store-basket-widget">';
	if (!isset($_SESSION['online-store'])) {
		$_SESSION['online-store']=array('items'=>array(),'total'=>0);
	}
	if (isset($vars->template) && $vars->template) {
		$t=$vars->template;
		$t=str_replace('{{ONLINESTORE_NUM_ITEMS}}', OnlineStore_getNumItems(), $t);
		$t=str_replace('{{ONLINESTORE_FINAL_TOTAL}}', OnlineStore_numToPrice(OnlineStore_getFinalTotal()), $t);
		$t=str_replace('{{ONLINESTORE_CHECKOUTURL}}', '/?pageid='.$_SESSION['onlinestore_checkout_page'], $t);
		$html.=$t;
	}
	else {
		if (count($_SESSION['online-store']['items'])) {
			$html.='<table class="os_basket">';
			$html.='<tr class="os_basket_titles"><th>&nbsp;</th><th>Price</th><th>Amount</th>'
				.'<th>Total</th></tr>';
			foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
				// { name
				$html.='<tr class="os_basket_itemTitle" product="'.$md5.'">'
					.'<th colspan="4">';
				if ($item['url']) {
					$html.='<a href="'.$item['url'].'">';
				}
				$html.=$item['short_desc'];
				if ($item['url']) {
					$html.='</a>';
				}
				$html.='</th></tr>';
				// }
				$html.='<tr class="os_basket_itemDetails '.$md5.'" product="'.$md5.'">'
					.'<td>&nbsp;</td><td>'
					.OnlineStore_numToPrice($item['cost']).'</td>';
				// { amount
				$html.='<td class="amt"><span class="'.$md5.'-amt">'.$item['amt']
					.'</span></td>';
				// }
				$html.='<td class="'.$md5.'-item-total">'
					.OnlineStore_numToPrice($item['cost']*$item['amt'])
					.'</td></tr>';
			}
			$html.='<tr class="os_basket_totals"><th colspan="3">Total</th>'
				.'<td class="total">'
				.OnlineStore_numToPrice($_SESSION['online-store']['total']).'</td></tr>';
			$html.='</table>';
			$html.='<a href="/?pageid='.$_SESSION['onlinestore_checkout_page'].'">'
				.'Proceed to Checkout</a>';
		}
		else {
			$html.='<em>empty</em>';
		}
	}
	$html.='</div>';
	WW_addScript('/ww.plugins/online-store/j/basket.js');
	return $html;
}

/**
	* get data about postage and packaging
	*
	* @param float  $total   basket value
	* @param string $country country that the purchaser is in
	* @param float  $weight  the weight of the basket
	*
	* @return array
	*/
function OnlineStore_getPostageAndPackaging($total,$country,$weight){
	if (!OnlineStore_getNumItems()) {
		return array('name'=>'none', 'total'=>0);
	}
	$pandps=OnlineStore_getPostageAndPackagingData();
	if (!isset($_SESSION['os_pandp'])) {
		$_SESSION['os_pandp']=0;
	}
	$pid=$_SESSION['os_pandp'];
	if (!isset($pandps[$pid]) || $pandps[$pid]->name=='') {
		$pid=0;
	}
	$pandp=$pandps[$pid];
	return array('name'=>$pandp->name,'total'=>OnlineStore_getPostageAndPackagingSubtotal($pandp->constraints,$total,$country,$weight));
}
function OnlineStore_getPostageAndPackagingData(){
	$p=Page::getInstance($_SESSION['onlinestore_checkout_page']);
	$p->initValues();
	$r=$p->vars['online_stores_postage'];
	if ($r=='' || $r=='[]') {
		$r='[{"name":"no postage and packaging set","constraints":[{"type":"set_value","value":"0"}]}]';
	}
	return json_decode($r);
}   
function OnlineStore_getPostageAndPackagingSubtotal($cstrs,$total,$country,$weight){
	foreach($cstrs as $cstr){
		if ($cstr->type=='total_weight_less_than_or_equal_to' && $weight<=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal($cstr->constraints,$total,$country,$weight);
		}
		if ($cstr->type=='total_weight_more_than_or_equal_to' && $weight>=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal($cstr->constraints,$total,$country,$weight);
		}
		if ($cstr->type=='total_less_than_or_equal_to' && $total<=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal($cstr->constraints,$total,$country,$weight);
		}
		if ($cstr->type=='total_more_than_or_equal_to' && $total>=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal($cstr->constraints,$total,$country,$weight);
		}
	}
	$val=str_replace('weight',$weight,$cstr->value);
	$val=str_replace('total',$total,$val);
	$val=str_replace('num_items',OnlineStore_getNumItems(),$val);
	$val=preg_replace('#[^a-z0-9*/\-+.\(\)]#','',$val);
	if (preg_match('/[^0-9.]/',str_replace('ceil','',$val))) {
		eval('$val=('.$val.');');
	}
	return (float)$val;
}
function OnlineStore_getFinalTotal() {
	$grandTotal = 0;
	$vattable=0;
	$has_vatfree=false;
	foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
		$totalItemCost=$item['cost']*$item['amt'];
		$grandTotal+=$totalItemCost;
		if ($item['vat']) {
			$vattable+=$totalItemCost;
		}
	}
	$postage=OnlineStore_getPostageAndPackaging($grandTotal, '', 0);
	if ($postage['total']) {
		$grandTotal+=$postage['total'];
	}
	if ($vattable) {
		$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
		$grandTotal+=$vat;
	}
	return $grandTotal;
}

/**
	* returns the number of items in the cart
	*
	* @return int
	*/
function OnlineStore_getNumItems(){
	$num=0;
	$cart=&$_SESSION['online-store']['items'];
	foreach ($cart as $item) {
		$num+=$item['amt'];
	}
	return $num;
}
function OnlineStore_startup(){
	if (!isset($_SESSION['onlinestore_checkout_page'])) {
		$p=dbOne('select id from pages where type="online-store"','id');
		if ($p) {
			$_SESSION['onlinestore_checkout_page']=$p;
			$page=Page::getInstance($p);
			if ($page) {
				$page->initValues();
				$vat=isset($page->vars['online_stores_vat_percent'])
					?$page->vars['online_stores_vat_percent']
					:21;
				if ($vat=='') {
					$vat=21;
				}
				$_SESSION['onlinestore_vat_percent']=(float)$vat;
			}
		}
	}
	if (!isset($_SESSION['currency'])) {
		$currencies=dbOne(
			'select value from site_vars where name="currencies" limit 1',
			'value'
		);
		if ($currencies==false) {
			if (!isset($GLOBALS['DBVARS']['online_store_currency'])) {
				$GLOBALS['DBVARS']['online_store_currency']='EUR';
			}
			$currency=$GLOBALS['DBVARS']['online_store_currency'];
			$currency_symbols=array('EUR'=>'€','GBP'=>'£');
			$_SESSION['currency']=array(
				'name'   => $currency,
				'symbol' => $currency_symbols[$currency],
				'iso'    => $currency,
				'value'  => 1
			);
		}
		else {
			$currencies=json_decode($currencies, true);
			$_SESSION['currency']=$currencies[0];
		}
	}
}
