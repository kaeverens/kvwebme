<?php
/**
	* online-store admin api
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { OnlineStore_adminCapture

/**
	* capture a payment
	*
	* @return array status
	*/
function OnlineStore_adminCapture() {
	$ids=explode(',', $_REQUEST['ids']);
	$errors=array();
	$ok=array();
	foreach ($ids as $id) {
		$id=(int)$id;
		$r=dbRow(
			'select total, status, authorised, meta from online_store_orders'
			.' where id='.$id
		);
		if ($r['authorised']!=1) {
			$errors[]=__(
				'Transaction %1 is no longer authorised.',
				array($id),
				'core'
			)
				.' '.__('Maybe it was already captured?');
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
		$response = str_replace("\n", '', curl_exec($ch));
		curl_close($ch);
		if (strpos($response, 'qpstat>000<')!==false) {
			$meta['qpsuccess']=$response;
			$status=($r['status']<1||$r['status']==4)?1:$r['status'];
			dbQuery(
				'update online_store_orders set status='.$status.', authorised=0,'
				.'meta="'.addslashes(json_encode($meta)).'" where id='.$id
			);
			require_once dirname(__FILE__).'/order-status.php';
			OnlineStore_sendInvoiceEmail($id);
			OnlineStore_exportToFile($id);
			$ok[]=$id;
		}
		else {
			$meta['qpfail']=$response;
			dbQuery(
				'update online_store_orders set meta="'.addslashes(json_encode($meta))
				.'" where id='.$id
			);
			$switchkey=preg_replace('/.*<qpstat>([^<]*)<.*/', '\1', $response);
			switch ($switchkey) {
				case '004': // {
					$ok[]=$id;
					$errors[]=__(
						'Transaction %1 has already been captured.', array($id), 'core'
					);
					$status=$r['status']<1?1:$r['status'];
					dbQuery(
						'update online_store_orders set status='.$status.', authorised=0 '
						.'where id='.$id
					);
				break; // }
				default: // {
					$errors[]=__('unknown error on transaction %1', array($id), 'core')
						.': '.$switchkey;
					// }
			}
		}
	}
	return array(
		'errors'=>$errors,
		'ok'=>$ok
	);
}

// }
// { OnlineStore_adminChangeOrderStatus

/**
	* change the payment status of an Online-Store order
	*
	* @return array status
	*/
function OnlineStore_adminChangeOrderStatus() {
	$id=(int)$_REQUEST['id'];
	$status=(int)$_REQUEST['status'];
	
	$invoices_by_email=(int)dbOne(
		'select value from online_store_vars where name="invoices_by_email"',
		'value'
	);
	if ($status==1) { // paid
		require dirname(__FILE__).'/order-status.php';
		OnlineStore_processOrder($id);
	}
	elseif ($status==3) { // cancelled
		dbQuery('update online_store_orders set status='.$status.' where id='.$id);
		Core_trigger(
			'after-order-cancelled',
			dbRow('select * from online_store_orders where id='.$id)
		);
	}
	else {
		dbQuery('update online_store_orders set status='.$status.' where id='.$id);
		require dirname(__FILE__).'/order-status.php';
		OnlineStore_sendInvoiceEmail($id);
		OnlineStore_exportToFile($id);
	}
	return array('ok'=>1);
}

// }
// { OnlineStore_adminOrderItemsList

/**
	* retrieve a list of ordered items
	*
	* @return array
	*/
function OnlineStore_adminOrderItemsList() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select * from online_store_orders where id='.$id);
	if (!$r || !$r['items']) {
		return array('error'=>__('No such order'));
	}
	$items=array();
	foreach (json_decode($r['items'], true) as $item) {
		$items[]=array(
			'id'=>$item['id'],
			'name'=>(@$item['name']?$item['name']:$item['short_desc']),
			'amt'=>$item['amt']
		);
	}
	return $items;
}

// }
// { OnlineStore_adminOrdersExport

/**
	* export CSV file of paid orders
	*
	* @return array
	*/
function OnlineStore_adminOrdersExport() {
	$cdate=$_REQUEST['cdate'];
	$sql='select id from online_store_orders where (status=1 or authorised=1)'
		.' and date_created>"'.addslashes($cdate).'" order by date_created desc';
}

// }
// { OnlineStore_adminRedeemVoucher

/**
	* mark a voucher as redeemed
	*
	* @return string
	*/
function OnlineStore_adminRedeemVoucher() {
	$oid=(int)@$_REQUEST['oid'];
	$pid=@$_REQUEST['pid'];
	$order=dbRow('select * from online_store_orders where id='.$oid);
	$items=json_decode($order['items'], true);
	$item=$items[$pid];
	$items[$pid]['voucher_redeemed']=1;
	$order['items']=json_encode($items);
	dbQuery(
		'update online_store_orders set items="'.addslashes($order['items'])
		.'" where id='.$oid
	);
	echo '<p>'.__('This voucher has been marked as Redeemed.').'</p>';
	Core_quit();
}

// }
// { OnlineStore_adminUserGroupsGet

/**
	* get an array of user groups for customers
	*
	* @return array
	*/
function OnlineStore_adminUserGroupsGet() {
	$gname=addslashes(
		dbOne(
			'select value from page_vars'
			.' where name="online_stores_customers_usergroup"',
			'value'
		)
	);
	return dbAll(
		'select id,name from groups where name in ("'.$gname.'")'
		.' order by name'
	);
}

// }
// { OnlineStore_adminInvoicesGetAsPdf

/**
	* return a fwe invoices as a zipped collection of PDFs
	*
	* @return null
	*/
function OnlineStore_adminInvoicesGetAsPdf() {
	$ids=explode(',', $_REQUEST['ids']);
	$files=array();
	$foundIds=array();
	foreach ($ids as $id) {
		$id=(int)$id;
		$pfile=USERBASE.'/ww.cache/online-store/invoice'.$id.'.pdf';
		if (!file_exists($pfile)) {
			$hfile=USERBASE.'/ww.cache/online-store/invoice'.$id;
			if (!file_exists($hfile) || !filesize($hfile)) {
				$i=dbOne(
					'select invoice from online_store_orders where id='.$id,
					'invoice'
				);
				if (!$i) {
					continue;
				}
				file_put_contents(
					$hfile,
					"\xEF\xBB\xBF".'<html><head><meta http-equiv="Content-Type"'
					.' content="text/html;'
					.' charset=UTF-8" /></head><body>'.utf8_encode($i).'</body></html>'
				);
			}
			require_once $_SERVER['DOCUMENT_ROOT']
				.'/ww.incs/dompdf/dompdf_config.inc.php';
			$html=file_get_contents($hfile);
			$dompdf=new DOMPDF();
			$dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
			$dompdf->load_html(utf8_decode(str_replace('€', '&euro;', $html)), 'UTF-8');
			$dompdf->set_paper('a4');
			$dompdf->render();
			file_put_contents($pfile, $dompdf->output());
		}
		$files[]='invoice'.$id.'.pdf';
		$foundIds[]=$id;
	}
	$zdir=USERBASE.'/ww.cache/online-store/';
	$zfile=USERBASE.'/ww.cache/online-store/invoices-'.join(',', $foundIds).'.zip';
	$filesToZip=join(' ', $files);
	`cd $zdir && zip -D $zfile $filesToZip`;
	header('Content-type: application/zip');
	header('Content-Disposition: attachment; filename="invoices.zip"');
	$fp=fopen($zfile, 'r');
	fpassthru($fp);
	fclose($fp);
	Core_quit();
}

// }
