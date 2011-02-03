<?php
/**
	* This file contains functions that are payment-related, but not specifically
	* credit card or paypal related.
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
*/

/**
	* marks an order as Paid, sends an invoice, and calls any specified callbacks
	*
	* @param int   $id    ID of the order
	* @param array $order details of the order
	*
	* @return null
	*/
function OnlineStore_processOrder($id, $order) {
	// { mark order as paid
	dbQuery("UPDATE online_store_orders SET status='1' WHERE id=$id");
	// }
	// { call the callback if it's supplied
	if ($order['callback']) {
		file($order['callback']);
	}
	// }
	$form_vals=json_decode($order['form_vals']);
	$page=Page::getInstanceByType('online-store');
	$page->initValues();
	$from='noreply@'.str_replace('www.', '', $_SERVER['HTTP_HOST']);
	$bcc='';
	$page=Page::getInstanceByType('online-store');
	if ( $page
		&& isset($page->vars['online_stores_admin_email'])
		&& $page->vars['online_stores_admin_email']
	) {
		$from=$page->vars['online_stores_admin_email'];
		$bcc=$page->vars['online_stores_admin_email'];
	}
	if (isset($form_vals->email)) {
		$form_vals->Email=$form_vals->email;
	}
	if (!isset($form_vals->Email)) {
		$form_vals->Email='no-email-supplied@example.com';
	}
	$headers = "From: $from\r\nReply-To: $from\r\nX-Mailer: PHP/" . phpversion();
	$headers.='MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= 'To: '.$form_vals->Email. "\r\n";
	if ($bcc) {
		$headers.='BCC: '.$bcc."\r\n";
	}
	mail(
		$form_vals->Email,
		'['.str_replace('www.', '', $_SERVER['HTTP_HOST']).'] invoice #'. $id,
		$order['invoice'],
		$headers
	);
}
