<?php
/**
	* definition file for Online-Store plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'Online Store',
	'admin' => array(
		'page_type' => 'OnlineStore_adminPageForm',
		'menu' => array(
			'Online Store>Orders'              => 'list-pages',
			'Online Store>Vouchers'            => 'vouchers',
			'Online Store>Options'             => 'site-options',
			'Online Store>Create New Checkout' => 'wizard'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/online-store/admin/widget-form.php',
			'js_include' => '/ww.plugins/online-store/j/widget-admin.js'
		)
	),
	'description'=>'Add online-shopping capabilities to some plugins. '
		.'REQUIRES products plugin.',
	'frontend' => array(
		'widget' => 'OnlineStore_showBasketWidget',
		'page_type' => 'OnlineStore_frontend',
		'template_functions' => array(
			'ONLINESTORE_PAYMENT_TYPES' => array(
				'function' => 'OnlineStore_paymentTypes'
			),
			'ONLINESTORE_VOUCHER' => array(
				'function' => 'OnlineStore_showVoucherInput'
			),
			'PRODUCTS_FULL_PRICE' => array(
				'function' => 'OnlineStore_productPriceFull'
			)
		)
	),
	'triggers' => array(
		'displaying-pagedata'      => 'OnlineStore_pagedata',
		'initialisation-completed' => 'OnlineStore_startup',
		'privacy_user_profile'     => 'OnlineStore_userProfile'
	),
	'version' => '13'
);
// }
// { currency symbols
$online_store_currencies=array(
	'EUR'=>array('&euro;','Euro'),
	'GBP'=>array('&pound;','Pound Sterling')
);
// }

/**
  * lists past orders made by the user
  *
  * @param object $PAGEDATA the current page instance
  * @param int    $user     the user ID
  *
  * @return string HTML list of orders
  */
function OnlineStore_userProfile( $PAGEDATA, $user ) {
	require dirname(__FILE__).'/frontend/user-profile.php';
	return $html;
}

/**
	* adds a product to the cart
	*
	* @param float   $cost          cost of the product
	* @param int     $amt           how many to add
	* @param string  $short_desc    short description of the product
	* @param string  $long_desc     long description of the product
	* @param string  $md5           a unique key for this product in the session
	* @param string  $url           URL where the product can be viewed
	* @param boolean $vat           does VAT apply to this product
	* @param int     $id            the product's ID, if there is one
	* @param boolean $delivery_free is this product's delivery free
	* @param boolean $no_discount   does this product ignore discounts
	*
	* @return null
	*/
function OnlineStore_addToCart(
	$cost=0, $amt=0, $short_desc='', $long_desc='', $md5='', $url='',
	$vat=true, $id=0, $delivery_free=false, $no_discount=false
) {
	// { add item to session
	if (!isset($_SESSION['online-store'])) {
		$_SESSION['online-store']=array('items'=>array(),'total'=>0);
	}
	$item=(isset($_SESSION['online-store']['items'][$md5]))
		?$_SESSION['online-store']['items'][$md5]
		:array(
			'cost'=>0, 'amt'=>0, 'short_desc'=>$short_desc,
			'long_desc'=>preg_replace('/\|.*/', '', $long_desc),
			'url'=>$url
		);
	$item['cost']=$cost;
	$item['amt']+=$amt;
	$item['short_desc']=$short_desc;
	$item['url']=$url;
	$item['vat']=$vat;
	$item['id']=$id;
	$item['delivery_free']=$delivery_free;
	$item['not_discountable']=$no_discount;
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
	* @param string $return   URL the buyer should be returned to after a purchase
	*
	* @return string
	*/
function OnlineStore_generatePaypalButton($PAGEDATA, $id, $total, $return='') {
	require_once dirname(__FILE__).'/frontend/generate-button-paypal.php';
	return $html;
}

/**
	* return HTML for a QuickPay button to pay for the current Online-Store order
	*
	* @param object $PAGEDATA the checkout page
	* @param int    $id       the order ID
	* @param float  $total    the order total
	* @param string $return   URL the buyer should be returned to after a purchase
	*
	* @return string
	*/
function OnlineStore_generateQuickPayButton(
	$PAGEDATA, $id, $total, $return=''
) {
	require_once dirname(__FILE__).'/frontend/generate-button-quickpay.php';
	return $html;
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
	require_once dirname(__FILE__).'/frontend/generate-button-realex.php';
	return $html;
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
function OnlineStore_paymentTypes() {
	require_once dirname(__FILE__).'/frontend/payment-types.php';
	return $c;
}

/**
	* Smarty function for returning a product's price, including currency symbol
	*
	* @param array  $params parameters passed via Smarty
	* @param object $smarty the current Smarty object
	*
	* @return string
	*/
function OnlineStore_productPriceFull($params, $smarty) {
	require_once dirname(__FILE__).'/frontend/smarty-functions.php';
	return OnlineStore_productPriceFull2($params, $smarty);
}


/**
	* when given a number, it returns that number formatted to a currency
	*
	* @param float   $val     the number to convert
	* @param boolean $sym     whether to return a symbol as well
	* @param boomean $rounded should the returned value be rounded?
	*
	* @return string
	*/
function OnlineStore_numToPrice($val, $sym=true, $rounded=false) {
	$rate=$_SESSION['currency']['value'];
	$sym=$_SESSION['currency']['symbol'];
	return $sym.sprintf("%.2f", $rounded?round($val*$rate):$val*$rate);
}

/**
	* returns a HTML string to show the Online-Store basket
	*
	* @param array $vars parameters passed via Smarty
	*
	* @return string
	*/
function OnlineStore_showBasketWidget($vars=null) {
	global $DBVARS;
	$slidedown=@$vars->slidedown;
	$slideup=(int)@$vars->slideup_delay;
	$html='<div class="online-store-basket-widget'
		.($slidedown?' slidedown':'').'"'
		.'">';
	if ($slidedown) {
		$html.='<div class="slidedown-header">Your Items</div>'
			.'<div class="slidedown-wrapper" slidedown="'
			.@$vars->slidedown_animation.'" slideup="'.$slideup.'">';
		WW_addCSS('/ww.plugins/online-store/basket.css');
	}
	// { basket body
	if (!isset($_SESSION['online-store'])) {
		$_SESSION['online-store']=array('items'=>array(),'total'=>0);
	}
	if (@$vars->template) {
		$t=$vars->template;
		$t=str_replace('{{ONLINESTORE_NUM_ITEMS}}', OnlineStore_getNumItems(), $t);
		if (!@$_SESSION['onlinestore_checkout_page']) {
			OnlineStore_setCheckoutPage();
		}
		$t=str_replace(
			'{{ONLINESTORE_FINAL_TOTAL}}',
			OnlineStore_numToPrice(OnlineStore_getFinalTotal()),
			$t
		);
		$t=str_replace(
			'{{ONLINESTORE_CHECKOUTURL}}',
			'/?pageid='.$_SESSION['onlinestore_checkout_page'],
			$t
		);
		$html.=$t;
	}
	else {
		if (count($_SESSION['online-store']['items'])) {
			$html.='<table class="os_basket">';
			$html.='<tr class="os_basket_titles"><th>Price</th><th>Amt</th>'
				.'<th>Total</th></tr>';
			foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
				// { name
				$html.='<tr class="os_basket_itemTitle" product="'.$md5.'">'
					.'<th colspan="3">';
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
					.'<td>'.OnlineStore_numToPrice($item['cost']).'</td>';
				// { amount
				$html.='<td class="amt"><span class="'.$md5.'-amt">'.$item['amt'].'</span>'
					.' [<a title="remove" class="amt-del" href="javascript:;">x</a>]'
					.'</td>';
				// }
				$html.='<td class="'.$md5.'-item-total">'
					.OnlineStore_numToPrice($item['cost']*$item['amt'])
					.'</td></tr>';
			}
			$html.='<tr class="os_basket_totals"><th colspan="2">Total</th>'
				.'<td class="total">'
				.OnlineStore_numToPrice($_SESSION['online-store']['total'])
				.'</td></tr>'
				.'</table>'
				.'<a class="online-store-checkout-link" href="/?pageid='
				.$_SESSION['onlinestore_checkout_page'].'">'
				.'Proceed to Checkout</a>';
		}
		else {
			$html.='<em>empty</em>';
		}
	}
	if (@$_SESSION['userdata']['id']) {
		$html.='<div id="onlinestore-lists"><span>Lists: </span>'
			.'<a href="javascript:;" class="onlinestore-load-list">load</a>';
		if (count(@$_SESSION['online-store']['items'])) {
			$html.=' | <a href="javascript:;" class="onlinestore-save-list">save</a>';
		}
		$html.='</div>';
	}
	// }
	if ($slidedown) {
		$html.='</div>';
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
function OnlineStore_getPostageAndPackaging($total, $country, $weight) {
	if (!OnlineStore_getNumItems() || !$total) {
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
	return array(
		'name'=>$pandp->name,
		'total'=>OnlineStore_getPostageAndPackagingSubtotal(
			$pandp->constraints, $total, $country, $weight
		)
	);
}

/**
	* get postage and packaging constraints for current checkout
	*
	* @return object the constraints
	*/
function OnlineStore_getPostageAndPackagingData() {
	$p=Page::getInstance($_SESSION['onlinestore_checkout_page']);
	$p->initValues();
	$r=@$p->vars['online_stores_postage'];
	if ($r=='' || $r=='[]') {
		$r='[{"name":"no postage and packaging set","constraints":[{"type":"set'
			.'_value","value":"0"}]}]';
	}
	return json_decode($r);
}   

/**
	* figure out the p&p cost
	*
	* @param object $cstrs   constraints - rules for figuring out p&p
	* @param float  $total   total value of the checkout
	* @param string $country the country being delivered to
	* @param float  $weight  the weight of the basket
	*
	* @return float the p&p cost
	*/
function OnlineStore_getPostageAndPackagingSubtotal(
	$cstrs, $total, $country, $weight
) {
	foreach ($cstrs as $cstr) {
		if ($cstr->type=='total_weight_less_than_or_equal_to'
			&& $weight<=$cstr->value
		) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
		if ($cstr->type=='total_weight_more_than_or_equal_to'
			&& $weight>=$cstr->value
		) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
		if ($cstr->type=='total_less_than_or_equal_to' && $total<=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
		if ($cstr->type=='total_more_than_or_equal_to' && $total>=$cstr->value) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
		if ($cstr->type=='numitems_less_than_or_equal_to'
			&& OnlineStore_getNumItems()<=$cstr->value
		) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
		if ($cstr->type=='numitems_more_than_or_equal_to'
			&& OnlineStore_getNumItems()>=$cstr->value
		) {
			return OnlineStore_getPostageAndPackagingSubtotal(
				$cstr->constraints, $total, $country, $weight
			);
		}
	}
	$val=str_replace('weight', $weight, $cstr->value);
	$val=str_replace('total', $total, $val);
	$val=str_replace('num_items', OnlineStore_getNumItems(), $val);
	$val=preg_replace('#[^a-z0-9*/\-+.\(\)]#', '', $val);
	if (preg_match('/[^0-9.]/', str_replace('ceil', '', $val))) {
		eval('$val=('.$val.');');
	}
	return (float)$val;
}

/**
	* return the grand total in the checkout
	*
	* @return float
	*/
function OnlineStore_getFinalTotal() {
	$grandTotal = 0;
	$deliveryTotal=0;
	$discountableTotal=0;
	$vattable=0;
	$has_vatfree=false;
	$user_is_vat_free=0;
	$group_discount=0;
	if (@$_SESSION['userdata']['id']) {
		$user=User::getInstance($_SESSION['userdata']['id']);
		$user_is_vat_free=$user->isInGroup('_vatfree');
		$group_discount=$user->getGroupHighest('discount');
	}
	foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
		$totalItemCost=$item['cost']*$item['amt'];
		$grandTotal+=$totalItemCost;
		if ($item['vat']) {
			$vattable+=$totalItemCost;
		}
		if (!isset($item['delivery_free']) || !$item['delivery_free']) {
			$deliveryTotal+=$totalItemCost;
		}
		if (!isset($item['not_discountable']) || !$item['not_discountable']) {
			$discountableTotal+=$totalItemCost;
		}
	}
	if (@$_REQUEST['os_voucher']) {
		require_once dirname(__FILE__).'/frontend/voucher-libs.php';
		$email=@$_REQUEST['Email'];
		$code=$_REQUEST['os_voucher'];
		$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
		if ($voucher_amount) {
			$grandTotal-=$voucher_amount;
		}
	}
	if ($group_discount && $discountableTotal) { // group discount
		$discount_amount=$discountableTotal*($group_discount/100);
		$grandTotal-=$discount_amount;
	}
	// { postage
	$postage=OnlineStore_getPostageAndPackaging($deliveryTotal, '', 0);
	if ($postage['total']) {
		$grandTotal+=$postage['total'];
	}
	// }
	if ($vattable && !$user_is_vat_free) {
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
function OnlineStore_getNumItems() {
	$num=0;
	if (!isset($_SESSION['online-store']['items'])) {
		return 0;
	}
	$cart=&$_SESSION['online-store']['items'];
	foreach ($cart as $item) {
		$num+=$item['amt'];
	}
	return $num;
}

/**
  * initialise the online store
  *
  * @return null
  */
function OnlineStore_startup() {
	if (!isset($_SESSION['onlinestore_checkout_page'])) {
		OnlineStore_setCheckoutPage();
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

function OnlineStore_setCheckoutPage() {
	$p=dbOne('select id from pages where type like "online-store%"', 'id');
	if ($p) {
		$_SESSION['onlinestore_checkout_page']=$p;
		$page=Page::getInstance($p);
		if ($page) {
			$page->initValues();
			$vat=isset($page->vars['online_stores_vat_percent'])
				?$page->vars['online_stores_vat_percent']
				:0;
			if ($vat=='') {
				$vat=0;
			}
			$_SESSION['onlinestore_vat_percent']=(float)$vat;
		}
	}
}
