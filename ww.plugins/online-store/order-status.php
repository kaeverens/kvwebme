<?php
/**
	* This file contains functions that are payment-related, but not specifically
	* credit card or paypal related.
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
*/

// { OnlineStore_processOrder

/**
	* marks an order as Paid, sends an invoice, and calls any specified callbacks
	*
	* @param int   $id    ID of the order
	* @param array $order details of the order
	*
	* @return null
	*/
function OnlineStore_processOrder($id, $order=false) {
	if ($order===false) {
		$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
	}
	$items=json_decode($order['items'], true);
	// { mark order as paid
	dbQuery("UPDATE online_store_orders SET status='1' WHERE id=$id");
	OnlineStore_updateProductSales($id, $items, $order['date_created']);
	if (!$order['invoice_num']) {
		$highest=dbOne('select invoice_num from online_store_orders order by invoice_num desc limit 1', 'invoice_num');
		$order['invoice_num']=$highest+1;
		dbQuery('update online_store_orders set invoice_num='.$order['invoice_num'].' where id='.$id);
	}
	$order['status']=1;
	// }
	// { call the callback if it's supplied
	if ($order['callback']) {
		file($order['callback']);
	}
	// }
	Core_trigger('after-order-processed', array($order));
	OnlineStore_sendInvoiceEmail($id, $order);
	OnlineStore_exportToFile($id, $order);
}

// }
// { OnlineStore_sendInvoiceEmail

/**
	* sends an invoice if the status is right
	*
	* @param int   $id    ID of the order
	* @param array $order details of the order
	*
	* @return null
	*/
function OnlineStore_sendInvoiceEmail($id, $order=false) {
	if ($order===false) {
		$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
	}
	$sendAt=(int)dbOne(
		'select val from online_store_vars where name="invoices_by_email"',
		'val'
	);
	if ($sendAt==0 && $order['status']!='1') {
		return;
	}
	if ($sendAt==1) { // never send
		return;
	}
	if ($sendAt==2 && $order['status']!='2') {
		return;
	}
	if ($sendAt==3 && $order['status']!='4') {
		return;
	}
	$form_vals=json_decode($order['form_vals']);
	$items=json_decode($order['items']);
	$short_domain=str_replace('www.', '', $_SERVER['HTTP_HOST']);
	// { work out from/to
	$page=Page::getInstanceByType('online-store');
	$page->initValues();
	$from='noreply@'.$short_domain;
	$bcc='';
	if ( $page
		&& isset($page->vars['online_stores_admin_email'])
		&& $page->vars['online_stores_admin_email']
	) {
		$from=$page->vars['online_stores_admin_email'];
		$bcc=$page->vars['online_stores_admin_email'];
	}
	if (isset($form_vals->billing_email)) {
		$form_vals->Billing_Email=$form_vals->billing_email;
	}
	if (!isset($form_vals->Billing_Email)) {
		$form_vals->Billing_Email='no-email-supplied@example.com';
	}
	$headers='';
	if ($bcc) {
		$sendToAdmin=(int)dbOne(
			'select val from online_store_vars where name="invoices_by_email_admin"',
			'val'
		);
		if (!$sendToAdmin) {
			$headers.='BCC: '.$bcc."\r\n";
		}
	}
	// }
	Core_trigger('send-invoice', array($order));
	// { send invoice
	Core_mail(
		$form_vals->Billing_Email,
		'['.$short_domain.'] invoice #'. $id,
		$order['invoice'],
		$from,
		'_body',
		$headers
	);
	// }
	// { handle item-specific stuff (vouchers, stock control)
	foreach ($items as $item_index=>$item) {
		if (!$item->id) {
			continue;
		}
		$p=Product::getInstance($item->id);
		$pt=ProductType::getInstance($p->vals['product_type_id']);
		if ($pt->is_voucher) {
			$html=$pt->voucher_template;
			// { common replaces
			$html=str_replace(
				'{{$_name}}',
				$p->name,
				$html
			);
			$html=str_replace(
				'{{$description}}',
				$p->vals['description'],
				$html
			);
			$html=str_replace(
				'{{$_recipient}}',
				$form_vals->Billing_Email,
				$html
			);
			$html=str_replace(
				'{{$_amount}}',
				$p->vals['os_voucher_value'],
				$html
			);
			// }
			if (strpos($html, '{{PRODUCTS_QRCODE}}')!==false) { // qr code
				$url='http://'.$_SERVER['HTTP_HOST'].'/a/p=online-store/f=checkQrCode/'
					.'oid='.$order['id'].'/pid='.$item_index.'/md5='
					.md5($order['invoice']);
				$html=str_replace(
					'{{PRODUCTS_QRCODE}}',
					'<img src="http://'.$_SERVER['HTTP_HOST']
					.'/a/p=online-store/f=getQrCode/b64='
					.urlencode(base64_encode($url)).'"/>',
					$html
				);
			}
			Core_mail(
				$form_vals->Billing_Email,
				'['.$short_domain.'] voucher',
				$html,
				$from,
				'_body',
				$headers
			);
		}
		// { stock control
		$valsOS=$p->vals['online-store'];
		$stock_amount=(int)@$valsOS['_stock_amt']-$item->amt;
		$valsOS['_stock_amt']=$stock_amount;
		$sold_amount=(int)@$valsOS['_sold_amt']+$item->amt;
		$valsOS['_sold_amt']=$sold_amount;
		dbQuery(
			'update products set'
			.' online_store_fields="'.addslashes(json_encode($valsOS)).'"'
			.', os_amount_in_stock='.$stock_amount
			.', os_amount_sold='.$sold_amount
			.', date_edited=now()'
			.' where id='.$item->id
		);
		// }
	}
	Core_cacheClear('products');
	// }
}

// }
// { OnlineStore_exportToFile

/**
	* exports to file if the status is right
	*
	* @param int   $id    ID of the order
	* @param array $order details of the order
	*
	* @return null
	*/
function OnlineStore_exportToFile($id) {
	$order=dbRow("SELECT * FROM online_store_orders WHERE id=$id");
	$sendAt=(int)dbOne(
		'select val from online_store_vars where name="export_at_what_point"',
		'val'
	);
	if ($sendAt==0 && $order['status']!='1') {
		return;
	}
	if ($sendAt==1) { // never send
		return;
	}
	if ($sendAt==2 && $order['status']!='2') {
		return;
	}
	if ($sendAt==3 && $order['status']!='4') {
		return;
	}
	$form_vals=json_decode($order['form_vals']);
	$items=json_decode($order['items']);
	// { start export
	$export=dbOne(
		'select val from online_store_vars where name="export_dir"',
		'val'
	);
	// TODO: ability to edit these values in the admin
	$exportcsv=array(
		'"Phone Number","Customer Name","Address 1","Address 2","Postcode",'
		.'"Email","Stock Number","Amt","Price","Item ID"'
	);
	// }
	// { handle item-specific stuff (vouchers, stock control)
	foreach ($items as $item_index=>$item) {
		if (!$item->id) {
			continue;
		}
		$p=Product::getInstance($item->id);
		$exportcsv[]= // { line to export
			'"'
			.str_replace('"', '""', @$form_vals->Billing_Phone)
			.'","'
			.str_replace(
				'"',
				'""',
				@$form_vals->Billing_FirstName.' '.@$form_vals->Billing_Surname
			)
			.'","'
			.str_replace('"', '""', @$form_vals->Billing_Street)
			.'","'
			.str_replace('"', '""', @$form_vals->Billing_Street2)
			.'","'
			.str_replace('"', '""', @$form_vals->Billing_Postcode)
			.' '.str_replace('"', '""', @$form_vals->Billing_Town)
			.'","'
			.str_replace('"', '""', @$form_vals->Billing_Email)
			.'","'
			.str_replace('"', '""', @$p->Billing_stock_number)
			.'","'
			.$item->amt
			.'","'
			.$item->cost
			.'","'
			.$item->id
			.'"'; // }
	}
	// }
	Core_cacheClear('products');
	if ($export && strpos($export, '..')===false) {
		$customer=dbOne(
			'select val from online_store_vars where name="export_customers"',
			'val'
		);
		if ($customer && strpos($customer, '..')===false) {
			$customer_filename=dbOne(
				'select val from online_store_vars'
				.' where name="export_customer_filename"',
				'val'
			);
			if (!$customer_filename) {
				$customer_filename='customer-{{$Billing_Email}}.csv';
			}
			$customer_filename=str_replace(array('/', '..'), '', $customer_filename);
			$bits=preg_match_all(
				'/{{\$([^}]*)}}/', // {
				$customer_filename,
				$matches,
				PREG_SET_ORDER
			);
			foreach ($matches as $bit) {
				$customer_filename=str_replace(
					'{{$'.$bit[1].'}}',
					@$form_vals->{$bit[1]},
					$customer_filename
				);
			}
			$customer_filename=str_replace(array('..', '/'), '', $customer_filename);
			@mkdir(USERBASE.'/'.$customer, 0777, true);
			$phone=preg_replace('/[^0-9\(\)\+]/', '', @$form_vals->Billing_Phone);
			// TODO: must be able to edit values in the admin
			$fcontent='"Name","Street","Street 2","Postcode","Email","Phone"'."\n"
				.'"'.str_replace(
					'"',
					'""',
					@$form_vals->Billing_FirstName.' '.@$form_vals->Billing_Surname
				)
				.'","'.str_replace(
					'"',
					'""',
					@$form_vals->Billing_Street
				)
				.'","'.str_replace(
					'"',
					'""',
					@$form_vals->Billing_Street2
				)
				.'","'.str_replace(
					'"',
					'""',
					@$form_vals->Billing_Postcode
				)
				.'","'.str_replace(
					'"',
					'""',
					@$form_vals->Billing_Email
				)
				.'","'.str_replace('"', '""', $form_vals->Billing_Phone).'"';
			file_put_contents(
				USERBASE.'/'.$customer.'/'.$customer_filename,
				"\xEF\xBB\xBF".$fcontent
			);
		}
		@mkdir(USERBASE.'/'.$export, 0777, true);
		file_put_contents(
			USERBASE.'/'.$export.'/order'.$id.'.csv',
			"\xEF\xBB\xBF".join("\r\n", $exportcsv)
		);
	}
}

// }
