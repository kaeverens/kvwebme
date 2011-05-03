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

function OnlineStore_showVoucherInput() {
	$code=@$_REQUEST['os_voucher'];
	return '<div id="os-voucher">Voucher Code: <input name="os_voucher" value="'
		.htmlspecialchars($code).'"/></div>';
}

if (isset($PAGEDATA->vars['online_stores_requires_login'])
	&& $PAGEDATA->vars['online_stores_requires_login']
	&& !isset($_SESSION['userdata'])
) {
	$c='<h2>Login Required</h2>'
		.'<p>You must be logged-in in order to use this online store. Please <a href="/_r?type=privacy">login / register</a> to access the checkout.</p>';
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
		if ($field->required && (!isset($_REQUEST[$name]) || !$_REQUEST[$name])) {
			$errors[]='You must enter the "'.htmlspecialchars($name).'" field.';
		}
	}
	// }
	// { if no payment method is selected, then choose the first available
	if (!isset($_REQUEST['_payment_method_type']) || $_REQUEST['_payment_method_type']=='') {
		if (isset($PAGEDATA->vars['online_stores_paypal_address'])
			&& $PAGEDATA->vars['online_stores_paypal_address']!=''
		) {
			$_REQUEST['_payment_method_type'] = 'PayPal';
		}
		else if (isset($PAGEDATA->vars['online_stores_realex_sharedsecret'])
			&& $PAGEDATA->vars['online_stores_realex_sharedsecret']
		) {
			$_REQUEST['_payment_method_type'] = 'Realex';
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
		case 'PayPal': // {
			if(
				!isset($PAGEDATA->vars['online_stores_paypal_address'])
				|| !$PAGEDATA->vars['online_stores_paypal_address']
			){
				$errors[]='PayPal payment method not available.';
			}
			break;
		// }
		case 'Realex': // {
			if(
				!isset($PAGEDATA->vars['online_stores_realex_sharedsecret'])
				|| !$PAGEDATA->vars['online_stores_realex_sharedsecret']
			){
				$errors[]='Realex payment method not available.';
			}
			break;
		// }
		default: // {
			$errors[]='Invalid payment method "'
				.htmlspecialchars($_REQUEST['_payment_method_type'])
				.'" selected.';
		// }
	}
	// }
	unset($_REQUEST['action']);
	unset($_REQUEST['page']);
	if (count($errors)) {
		$c.='<div class="errors"><em>'.join('</em><br /><em>', $errors)
			.'</em></div>';
	}
	else {
		$formvals=addslashes(json_encode($_REQUEST));
		$items=addslashes(json_encode($_SESSION['online-store']['items']));
		$total=OnlineStore_getFinalTotal();
		// { save data
		dbQuery(
			'insert into online_store_orders (form_vals,total,items,date_created)'
			." values('$formvals', $total, '$items', now())"
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
		$table='<table id="onlinestore-invoice" width="100%"><tr><th class="quantityheader">Quantity</th>'
			.'<th class="descriptionheader">Description</th>'
			.'<th class="unitamountheader">'
			.'Unit Price</th><th class="amountheader">Amount</th></tr>';
		$grandTotal=0;
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
		}
		$table.='<tr class="os_basket_totals">'
			.'<td colspan="3" style="text-align:right">'
			.'Subtotal</td><td class="totals amountcell">'
			.OnlineStore_numToPrice($total)
			.'</td></tr>';
		$postage=OnlineStore_getPostageAndPackaging($grandTotal, '', 0);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$table.='<tr><td class="p_and_p" style="text-align: right;" colspan="3">'
				.'Postage and Packaging (P&amp;P)</td><td class="amountcell">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		if (@$_REQUEST['os_voucher']) {
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$table.='<tr><td class="voucher" style="text-align: right;" colspan="3">'
					.'Voucher ('.htmlspecialchars($code).')</td><td class="totals amountcell">-'
					.OnlineStore_numToPrice($voucher_amount).'</td></tr>';
				$grandTotal-=$voucher_amount;
				OnlineStore_voucherRecordUsage($id, $voucher_amount);
			}
		}
		if ($vattable) {
			$table.='<tr><td style="text-align:right" class="vat" colspan="3">VAT ('.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="amountcell">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$table.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$table.='<tr class="os_basket_amountcell"><td style="text-align: right;" colspan="3">Total Due</td>'
			.'<td class="amountcell">'.OnlineStore_numToPrice($grandTotal).'</td></tr>';
		$table.='</table>';
		$smarty->assign('_invoice_table', $table);
		$smarty->assign('_invoicenumber', $id);
		// }
		$invoice=addslashes(
			$smarty->fetch(
				USERBASE.'ww.cache/online-store/'.$PAGEDATA->id
			)
		);
		dbQuery("update online_store_orders set invoice='$invoice' where id=$id");
		// }
		// { show payment button
		switch($_REQUEST['_payment_method_type']){
			case 'PayPal': // {
				$c.='<p>Your order has been recorded. Please click the button below '
					.'to go to PayPal for payment. Thank you.</p>';
				$c.=OnlineStore_generatePaypalButton($PAGEDATA, $id, $total);
				break;
			// }
			case 'Realex': // {
				$c.='<p>Your order has been recorded. Please click the button below '
					.'to go to Realex Payments for payment. Thank you.</p>';
				$c.=OnlineStore_generateRealexButton($PAGEDATA, $id, $total);
				break;
			// }
		}
		// }
		// { unset the shopping cart data
		unset($_SESSION['online-store']);
		// }
		$submitted=1;
	}
}

if (!$submitted) {
	if (
		isset($_SESSION['online-store'])
		&&isset($_SESSION['online-store']['items'])
		&&count($_SESSION['online-store']['items'])>0
	) {
		$user_is_vat_free=0;
		if (@$_SESSION['userdata']['id']) {
			$user=User::getInstance($_SESSION['userdata']['id']);
			$user_is_vat_free=$user->isInGroup('_vatfree');
		}
		$c.='<table id="onlinestore-checkout" width="100%"><tr>';
		$c.='<th>Item</th>';
		$c.='<th>Price</th>';
		$c.='<th>Amount</th>';
		$c.='<th>Total</th>';
		$c.='</tr>';
		$grandTotal = 0;
		$vattable=0;
		$has_vatfree=false;
		foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
			$c.='<tr product="'.$md5.'" class="os_item_numbers '.$md5.'"><td>';
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='<a href="'.$item['url'].'">';
			}
			$c.= htmlspecialchars($item['short_desc']);
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='</a>';
			}
			if (!$item['vat'] && !$user_is_vat_free) {
				$c.='<sup>1</sup>';
				$has_vatfree=true;
			}
			$c.='</td><td>'.OnlineStore_numToPrice($item['cost']).'</td>';
			$c.='<td class="amt"><span class="'.$md5.'-amt amt-num">'
				.$item['amt']
				.'</span></td>';
			$totalItemCost=$item['cost']*$item['amt'];
			$grandTotal+=$totalItemCost;
			if ($item['vat'] && !$user_is_vat_free) {
				$vattable+=$totalItemCost;
			}
			$c.='<td class="'.$md5.'-item-total totals">'
				.OnlineStore_numToPrice($totalItemCost).'</td></tr>';
			if ($item['long_desc']) {
				$c.='<tr><td colspan="3">'.$item['long_desc'].'</td><td></td></tr>';
			}
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspan="3">Subtotal</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>';
		$postage=OnlineStore_getPostageAndPackaging($grandTotal, '', 0);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$c.='<tr><td class="p_and_p" style="text-align: right;" colspan="3">'
				.'Postage and Packaging (P&amp;P)</td><td class="totals">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		if (@$_REQUEST['os_voucher']) {
			require_once dirname(__FILE__).'/voucher-libs.php';
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$c.='<tr><td class="voucher" style="text-align: right;" colspan="3">'
					.'Voucher ('.htmlspecialchars($code).')</td><td class="totals">-'
					.OnlineStore_numToPrice($voucher_amount).'</td></tr>';
				$grandTotal-=$voucher_amount;
			}
		}
		if ($vattable) {
			$c.='<tr><td style="text-align:right" class="vat" colspan="3">VAT ('.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="totals">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$c.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspan="3">Total Due</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>'
			.'</table>';
		if ($has_vatfree) {
			$c.='<div><sup>1</sup>VAT-free item</div>';
		}
		$c.='<form method="post">'
			.$PAGEDATA->render()
			.'<input type="submit" name="action" value="Proceed to Payment" />'
			.'</form>';
		WW_addScript('/ww.plugins/online-store/frontend/index.js');
	}
	else {
		$c.='<em>No items in your basket</em>';
	}
}
