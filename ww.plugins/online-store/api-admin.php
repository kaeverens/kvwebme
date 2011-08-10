<?php
function OnlineStore_adminCapture() {
	$ids=explode(',', $_REQUEST['ids']);
	$errors=array();
	foreach ($ids as $id) {
		$r=dbRow(
			'select total, authorised, meta from online_store_orders'
			.' where id='.(int)$id
		);
		if ($r['authorised']!=1) {
			$errors[]='transaction '.$id.' is no longer authorised.'
				.' maybe it was already captured?';
			continue;
		}
		$meta=json_decode($r['meta'], true);
		$merchantid=dbOne(
			'select value from page_vars,pages where page_id=pages.id and '
			.'pages.type="online-store" and '
			.'page_vars.name="online_stores_quickpay_merchantid"',
			'value'
		);
		$message=array(
			'protocol'=>4,
			'msgtype'=>'capture',
			'merchant'=>$merchantid,
			'amount'=>$meta['amount'],
			'transaction'=>$meta['transaction']
		);
		$md5fields=array(
			'protocol'=>4,
			'msgtype'=>'capture',
			'merchant'=>$merchantid,
			'amount'=>$meta['amount'],
			'transaction'=>$meta['transaction'],
			'secret'=>dbOne(
				'select value from page_vars,pages where page_id=pages.id and '
				.'pages.type="online-store" and '
				.'page_vars.name="online_stores_quickpay_secret"',
				'value'
			)
		);
		$message['md5check'] = md5(implode('', $md5fields));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://secure.quickpay.dk/api');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		return array(
			'errors'=>$response
		);
	}
	return count($errors)
		?array('errors'=>$errors)
		:array('ok'=>1);
}
