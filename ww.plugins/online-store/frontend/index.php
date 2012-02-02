<?php
/**
	* Online-Store front-end page type
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
  * function for showing list of countries selected
  *
	* @param array  $params  Smarty parameters
	* @param object &$smarty Smarty object
	*
  * @return string the HTML
  */
function OnlineStore_getCountriesSelectbox($params, &$smarty) {
	$page=Page::getInstance($_SESSION['onlinestore_checkout_page']);
	$cjson=$page->vars['online-store-countries'];
	$countries='<select name="'.(@$params['prefix']).'Country">'
		.'<option values="" class="__" lang-context="core"> -- choose -- </option>';
	if ($cjson) {
		$cjson=json_decode($cjson);
		foreach ($cjson as $country=>$val) {
			$countries.='<option>'.htmlspecialchars($country).'</option>';
		}
	}
	return $countries.'</select>';
}

/**
  * function for showing HTML of a voucher input
  *
  * @return string the HTML
  */
function OnlineStore_showVoucherInput() {
	$code=@$_REQUEST['os_voucher'];
	return '<div id="os-voucher"><span class="__">Voucher Code:</span> '
		.'<input name="os_voucher" value="'.htmlspecialchars($code).'"/></div>';
}

if (isset($PAGEDATA->vars['online_stores_requires_login'])
	&& $PAGEDATA->vars['online_stores_requires_login']
	&& !isset($_SESSION['userdata'])
) {
	$c='<h2 class="__" lang-context="core">Login Required</h2>'
		.'<p class="__" lang-context="core">You must be logged-in in order to '
		.'use this online store. Please '
		.'<a href="/_r?type=privacy">login / register</a> to access the checkout.'
		.'</p>';
	return;
}
WW_addScript('/ww.plugins/online-store/j/basket.js');
$c='';
global $DBVARS,$online_store_currencies;
$submitted=0;
if (@$_REQUEST['action'] && !(@$_REQUEST['os_no_submit']==1)) {
	$errors=array();
	// { check for errors in form submission
	$fields=$PAGEDATA->vars['online_stores_fields'];
	if (!$fields) {
		$fields='{}';
	}
	$fields=json_decode($fields);
	foreach ($fields as $name=>$field) { 
		if (!$field->show) {
			continue;
		} 
		if (@$field->required && (!isset($_REQUEST[$name]) || !$_REQUEST[$name])) {
			$errors[]='You must enter the "'.htmlspecialchars($name).'" field.';
		} 
	}
	// }
	// { if no payment method is selected, then choose the first available
	if (!isset($_REQUEST['_payment_method_type'])
		|| $_REQUEST['_payment_method_type']==''
	) {
		if (@$PAGEDATA->vars['online_stores_paypal_address']) {
			$_REQUEST['_payment_method_type'] = 'PayPal';
		}
		elseif (@$PAGEDATA->vars['online_stores_quickpay_merchantid']) {
			$_REQUEST['_payment_method_type'] = 'QuickPay';
		}
		elseif (@$PAGEDATA->vars['online_stores_realex_sharedsecret']) {
			$_REQUEST['_payment_method_type'] = 'Realex';
		}
		elseif (@$PAGEDATA->vars['online_stores_bank_transfer_account_number']) {
			$_REQUEST['_payment_method_type'] = 'Bank Transfer';
		}
	}
	// }
	// { if a voucher is submitted, check that it's still valid
	if (@$_REQUEST['os_voucher']) {
		require_once dirname(__FILE__).'/voucher-libs.php';
		$email=$_REQUEST['Email'];
		$code=$_REQUEST['os_voucher'];
		$valid=OnlineStore_voucherCheckValidity($code, $email);
		if (isset($valid['error'])) {
			$errors[]=$valid['error'];
		}
	}
	// }
	// { check that payment method is valid
	switch($_REQUEST['_payment_method_type']){
		case 'Bank Transfer': // {
			if (!@$PAGEDATA->vars['online_stores_bank_transfer_account_number']) {
				$errors[]='Bank Transfer payment method not available.';
			}
		break; // }
		case 'PayPal': // {
			if (!@$PAGEDATA->vars['online_stores_paypal_address']) {
				$errors[]='PayPal payment method not available.';
			}
		break; // }
		case 'QuickPay': // {
			if (!@$PAGEDATA->vars['online_stores_quickpay_secret']) {
				$errors[]='QuickPay payment method not available.';
			}
		break; // }
		case 'Realex': // {
			if (!@$PAGEDATA->vars['online_stores_realex_sharedsecret']) {
				$errors[]='Realex payment method not available.';
			}
		break; // }
		default: // {
			$errors[]='Invalid payment method "'
				.htmlspecialchars($_REQUEST['_payment_method_type'])
				.'" selected.';
			// }
	}
	// }
	// { check if new address was entered
	if (isset($_SESSION['userdata'])&&isset($_POST['save-address'])) {
		$_user=dbRow(
			'select address from user_accounts where id='.$_SESSION['userdata']['id']
		);
		$address=json_decode($_user['address'], true);
		$address[$_POST['save-address']]=array(
			'street'=>$_POST['Street'],
			'street2'=>$_POST['Street2'],
			'town'=>$_POST['Town'],
			'county'=>$_POST['County'],
			'country'=>$_POST['Country'],
		);
		$address=addslashes(json_encode($address));
		dbQuery(
			'update user_accounts set address="'.$address.'" where id='
			.$_SESSION['userdata']['id']
		);
	}
	// }
	unset($_REQUEST['action'], $_REQUEST['page']);
	if (count($errors)) {
		$c.='<div class="errors"><em class="__" lang-context="core">'
			.join('</em><br /><em class="__" lang-context="core">', $errors)
			.'</em></div>';
	} 
	else {
		$formvals = addslashes(json_encode($_REQUEST));
		$items=addslashes(json_encode($_SESSION['online-store']['items']));
		$total=OnlineStore_getFinalTotal();
		// { save data
		dbQuery(
			'insert into online_store_orders (form_vals,total,items,date_created,user_id)'
			." values('$formvals', $total, '$items', now(), '"
			. @$_SESSION[ 'userdata' ][ 'id' ] . "' )"
		);
		$id=dbOne('select last_insert_id() as id', 'id');
		 // }
		// { generate invoice
		require_once SCRIPTBASE . 'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
		$smarty = new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		if (!file_exists(USERBASE.'/ww.cache/templates_c')) {
			mkdir(USERBASE.'/ww.cache/templates_c');
		}
		$smarty->register_function('INVOICETABLE', 'online_store_invoice_table');
		foreach ($_REQUEST as $key=>$val) {
			$smarty->assign($key, $val);
		}
		// { table of items
		$table='<table id="onlinestore-invoice" style="clear:both" width="100%"'
			.'><tr><th class="quantityheader __" lang-context="core">Quantity</th>'
			.'<th class="descriptionheader __" lang-context="core">Description</th>'
			.'<th class="unitamountheader __" lang-context="core">Unit Price</th>'
			.'<th class="amountheader" class="__" lang-context="core">Amount</th>'
			.'</tr>';
		$user_is_vat_free=0;
		$group_discount=0;
		if (@$_SESSION['userdata']['id']) {
			$user=User::getInstance($_SESSION['userdata']['id']);
			$user_is_vat_free=$user->isInGroup('_vatfree');
			$group_discount=$user->getGroupHighest('discount');
		}
		$grandTotal=0;
		$discountableTotal=0;
		$deliveryTotal=0;
		$vattable=0;
		$has_vatfree=false;
		foreach ($_SESSION['online-store']['items'] as $key=>$item) {
			$totalItemCost=$item['cost']*$item['amt'];
			$table.='<tr><td class="quantitycell">'.$item['amt']
				.'</td><td class="descriptioncell"><a href="'.$item['url'].'">'
				.preg_replace('/<[^>]*>/', '', $item['short_desc'])
				.'</td><td class="unitamountcell">'
				.OnlineStore_numToPrice($item['cost'])
				.'</td><td class="amountcell">'
				.OnlineStore_numToPrice($totalItemCost)
				.'</td></tr>';
			if ($item['long_desc']) {
				$table.='<tr><td colspan="3">'.$item['long_desc'].'</td><td></td></tr>';
			}
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
		$table.='<tr class="os_basket_totals">'
			.'<td colspan="2" class="nobord">&nbsp;</td>'
			.'<td style="text-align:right" class="__" lang-context="core">'
			.'Subtotal</td><td class="totals amountcell">'
			.OnlineStore_numToPrice($grandTotal)
			.'</td></tr>';
		if (@$_REQUEST['os_voucher']) {
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$table.='<tr><td colspan="2" class="nobord">&nbsp;</td>'
					.'<td class="voucher" style="text-align: right;">'
					.'<span class="__" lang-context="core">Voucher</span> '
					.'('.htmlspecialchars($code).')</td><td class="totals amountcell">-'
					.OnlineStore_numToPrice($voucher_amount).'</td></tr>';
				$grandTotal-=$voucher_amount;
				OnlineStore_voucherRecordUsage($id, $voucher_amount);
			}
		}
		if ($group_discount) { // group discount
			$discount_amount=$grandTotal*($group_discount/100);
			$table.='<tr><td colspan="2" class="nobord">&nbsp;</td><td class="gro'
				.'up-discount" style="text-align:right;"><span class="__" lang-context="core">'
				.'Group Discount</span> ('.$group_discount.'%)</td><td class="totals">-'
				.OnlineStore_numToPrice($discount_amount).'</td></tr>';
			$grandTotal-=$discount_amount;
		}
		// { postage
		$postage=OnlineStore_getPostageAndPackaging($deliveryTotal, '', 0);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$table.='<tr><td colspan="2" class="nobord">&nbsp;</td><td class="p_a'
				.'nd_p __" lang-context="core" style="text-align: right;">'
				.'Postage and Packaging (P&amp;P)</td><td class="amountcell">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		// }
		if ($vattable && $_SESSION['onlinestore_vat_percent']) {
			$table.='<tr><td colspan="2" class="nobord">&nbsp;</td>'
				.'<td style="text-align:right" class="vat">'
				.'<span class="__" lang-context="core">VAT</span> '
				.'('.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="amountcell">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$table.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$table.='<tr class="os_basket_amountcell"><td colspan="2" class="nobord">'
			.'&nbsp;</td><td class="totalcell __" lang-context="core" '
			.'style="text-align: right;">Total Due</td>'
			.'<td class="amountcell">'.OnlineStore_numToPrice($grandTotal)
			.'</td></tr>';
		$table.='</table>';
		$smarty->assign('_invoice_table', $table);
		$smarty->assign('_invoicenumber', $id);
		// }
		if (!file_exists(USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id)) {
			@mkdir(USERBASE.'/ww.cache/online-store');
			file_put_contents(
				USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id,
				$PAGEDATA->vars['online_stores_invoice']
			);
		}
		$invoice=addslashes(
			$smarty->fetch(
				USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id
			)
		);
		dbQuery("update online_store_orders set invoice='$invoice' where id=$id");
		// }
		// { show payment button
		switch($_REQUEST['_payment_method_type']){
			case 'Bank Transfer': // {
				$msg=$PAGEDATA->vars['online_stores_bank_transfer_message'];
				$msg=str_replace(
					'{{$total}}',
					OnlineStore_numToPrice($grandTotal),
					$msg
				);
				$msg=str_replace(
					'{{$invoice_number}}',
					$id,
					$msg
				);
				$msg=str_replace(
					'{{$bank_name}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_bank_name']
					),
					$msg
				);
				$msg=str_replace(
					'{{$account_name}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_account_name']
					),
					$msg
				);
				$msg=str_replace(
					'{{$account_number}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_account_number']
					),
					$msg
				);
				$msg=str_replace(
					'{{$sort_code}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_sort_code']
					),
					$msg
				);
				$c.=$msg;
			break; // }
			case 'PayPal': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to PayPal for payment. '
					.'Thank you.</p>';
				$c.=OnlineStore_generatePaypalButton($PAGEDATA, $id, $total);
			break; // }
			case 'QuickPay': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to QuickPay for payment. '
					.'Thank you.</p>';
				$c.=OnlineStore_generateQuickPayButton($PAGEDATA, $id, $total);
			break; // }
			case 'Realex': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to Realex Payments for '
					.'payment. Thank you.</p>';
				$c.=OnlineStore_generateRealexButton($PAGEDATA, $id, $total);
			break; // }
		}
		// }
		// { unset the shopping cart data
		unset($_SESSION['online-store']);
		// }
		$submitted=1;
	} 
}

if (!$submitted) {
	if (@$_SESSION['online-store']['items']
		&& count($_SESSION['online-store']['items'])>0
	) {
		$viewtype=(int)$_REQUEST['viewtype'];
		$pviewtype=(int)@$PAGEDATA->vars['onlinestore_viewtype'];
		// { show basket contents
		$user_is_vat_free=0;
		$group_discount=0;
		if (@$_SESSION['userdata']['id']) {
			$user=User::getInstance($_SESSION['userdata']['id']);
			$user_is_vat_free=$user->isInGroup('_vatfree');
			$group_discount=$user->getGroupHighest('discount');
		}
		$c.='<table id="onlinestore-checkout" width="100%"><tr>';
		$c.='<th style="width:60%" class="__" lang-context="core">Item</th>';
		$c.='<th class="__" lang-context="core">Price</th>';
		$c.='<th class="__" lang-context="core">Amount</th>';
		$c.='<th class="totals __" lang-context="core">Total</th>';
		$c.='</tr>';
		$grandTotal = 0;
		$deliveryTotal=0;
		$discountableTotal=0;
		$vattable=0;
		$has_vatfree=false;
		foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
			$c.='<tr product="'.$md5.'" class="os_item_numbers '.$md5.'">';
			// { item name and details
			$c.='<td class="products-itemname">';
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='<a href="'.$item['url'].'">';
			}
			$c.= htmlspecialchars(__FromJson($item['short_desc']));
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='</a>';
			}
			if (!$item['vat'] && !$user_is_vat_free) {
				$c.='<sup>1</sup>';
				$has_vatfree=true;
			}
			$c.='</td>';
			// }
			// { cost per item
			$c.='<td>'.OnlineStore_numToPrice($item['cost']).'</td>';
			// }
			// { amount
			$c.='<td class="amt"><span class="'.$md5.'-amt amt-num">'
				.$item['amt']
				.'</span></td>';
			// }
			// { total cost of the item
			$totalItemCost=$item['cost']*$item['amt'];
			$grandTotal+=$totalItemCost;
			if ($item['vat'] && !$user_is_vat_free) {
				$vattable+=$totalItemCost;
			}
			if (!(@$item['delivery_free'])) {
				$deliveryTotal+=$totalItemCost;
			}
			if (!isset($item['not_discountable']) || !$item['not_discountable']) {
				$discountableTotal+=$totalItemCost;
			}
			$c.='<td class="'.$md5.'-item-total totals">'
				.OnlineStore_numToPrice($totalItemCost).'</td>';
			// }
			$c.='</tr>';
			if ($item['long_desc']) {
				$c.='<tr><td colspan="3" class="products-longdescription">'
					.$item['long_desc'].'</td><td></td></tr>';
			}
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspa'
			.'n="3" class="__" lang-context="core">Subtotal</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>';
		if (@$_REQUEST['os_voucher']) {
			require_once dirname(__FILE__).'/voucher-libs.php';
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$c.='<tr><td class="voucher" style="text-align: right;" colspan="3">'
					.'<span class="__" lang-context="core">Voucher</span> ('
					.htmlspecialchars($code).')</td><td class="totals">-'
					.OnlineStore_numToPrice($voucher_amount).'</td></tr>';
				$grandTotal-=$voucher_amount;
			}
		}
		if ($group_discount && $discountableTotal) { // group discount
			$discount_amount=$discountableTotal*($group_discount/100);
			$c.='<tr><td class="group-discount" style="text-align:right;" '
				.'colspan="3"><span class="__" lang-context="core">Group Discount'
				.'</span> ('.$group_discount.'%)</td><td class="totals">-'
				.OnlineStore_numToPrice($discount_amount).'</td></tr>';
			$grandTotal-=$discount_amount;
		}
		// { postage
		$postage=OnlineStore_getPostageAndPackaging(
			$deliveryTotal,
			@$_REQUEST['Country'],
			0
		);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$c.='<tr><td class="p_and_p __" lang-context="core" '
				.'style="text-align: right;" colspan="3">'
				.'Postage and Packaging (P&amp;P)</td><td class="totals">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		// }
		if ($vattable && $_SESSION['onlinestore_vat_percent']) {
			$c.='<tr><td style="text-align:right" class="vat" colspan="3">'
				.'<span class="__" lang-context="core">VAT</span> ('
				.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="totals">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$c.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspa'
			.'n="3" class="__" lang-context="core">Total Due</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>'
			.'</table>';
		if ($has_vatfree) {
			$c.='<div><sup>1</sup><span class="__" lang-context="core">'
				.'VAT-free item</span></div>';
		}
		// }
		// { show details form
		$_POST['_viewtype']=$pviewtype;
		if ($pviewtype==1&&$viewtype==1 || !$pviewtype) {
			$c.='<form method="post">'
				.$PAGEDATA->render()
				.'<input type="hidden" name="action" value="Proceed to Payment" />'
				.'<button class="__" lang-context="core">Proceed to Payment</button>'
				.'</form>';
		}
		else if ($pviewtype==2) {
			$c.='<div id="online-store-wrapper" class="online-store"></div>';
		}
		else {
			$c.='<form method="post"><input type="hidden" name="viewtype" value="1"/>'
				.'<button class="onlinestore-view-checkout __" lang-context="core">'
				.'Checkout</button></form>';
		}
		// }
		// { add scripts
		// { set up variables
		$post=$_POST;
		unset($post['action']);
		$postage=dbOne(
			'select value from page_vars where page_id=1302 and '
			.'name="online_stores_postage"',
			'value'
		);
		if (!$postage) {
			$post['_pandp']=0;
		}
		else {
			$post['_pandp']=count(json_decode($postage));
		}
		$post['os_pandp']=(int)$_SESSION['os_pandp'];
		// }
		WW_addInlineScript('var os_post_vars='.json_encode($post).';');
		WW_addScript('/ww.plugins/online-store/frontend/index.js');
		// }
	}
	else {
		$c.='<em class="__" lang-context="core">No items in your basket</em>';
	}
}
