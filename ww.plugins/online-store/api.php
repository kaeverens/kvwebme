<?php
/**
	* OnlineStore api functions
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { OnlineStore_addProductToCart

/**
	* checks that a product got successfully added to a cart.
	* or removes it if the item has expired.
	*
	* @return status
	*/
function OnlineStore_addProductToCart() {
	$id=(int)$_REQUEST['product_id'];
	$p=dbRow('select id,expires_on from products where id='.$id);
	if ($p && $p['expires_on']>date('Y-m-d')) {
		return array('ok'=>1);
	}
	unset($_SESSION['online-store']['items']['products_'.$id]);
	if (!$p) {
		return array('error'=>'does not exist');
	}
	return array('error'=>'expired', 'date_expired'=>$p['expires_on']);
}

// }
// { OnlineStore_getExpiryNotification

/**
	* retrieve message for when someone tries to add an expired item to cart
	*
	* @return string message
	*/
function OnlineStore_getExpiryNotification() {
	$id=(int)$_REQUEST['id'];
	$p=dbRow('select * from products where id='.$id);
	$product=Product::getInstance($id, $p, true);
	$typeid=$p['product_type_id'];
	$nfile=USERBASE.'/ww.cache/products/templates/expiry_notification_'.$typeid;
	if (!file_exists($nfile)) {
		$t=dbRow(
			'select template_expired_notification from products_types where id='
			.$typeid
		);
		$template=strlen($t['template_expired_notification'])>4
			?$t['template_expired_notification']
			:''.__('This product has expired. You cannot add it to the cart.').'';
		file_put_contents($nfile, $template);
	}
	require_once SCRIPTBASE.'/ww.incs/common.php';
	$smarty=Products_setupSmarty();
	$smarty->assign('product', $product);
	$smarty->assign('product_id', $product->get('id'));
	$smarty->assign('_name', __FromJson($product->name));
	$smarty->assign('_stock_number', $product->stock_number);
	return $smarty->fetch($nfile);
}

// }
// { OnlineStore_checkQrCode

/**
	* check a QR Code voucher to see if it's valid
	*
	* @return null
	*/
function OnlineStore_checkQrCode() {
	global $DBVARS;
	echo '<table style="width:100%"><tr><td><img src="/f/skin_files/logo.png"/>'
		.'</td><td><h1>'.$DBVARS['site_title'].'</h1><h3>'
		.$DBVARS['site_subtitle'].'</h3></td></tr></table><hr/>';
	$oid=(int)@$_REQUEST['oid'];
	$pid=@$_REQUEST['pid'];
	if (!$oid || !$pid) {
		echo ''.__('Product or order ID not found').'';
		Core_quit();
	}
	$order=dbRow('select * from online_store_orders where id='.$oid);
	if (!$order) {
		echo ''.__('Order ID not found.').'';
		Core_quit();
	}
	$md5=$_REQUEST['md5'];
	if ($md5!=md5($order['invoice'])) {
		echo ''.__('MD5 check failed. this voucher has been tampered with.').'';
		Core_quit();
	}
	echo '<h1>'.__('Valid Voucher').'</h1>';
	$items=json_decode($order['items'], true);
	$item=$items[$pid];
	echo '<h2>'.$item['short_desc'].'</h2>'.$item['long_desc'];
	if (!isset($item['voucher_redeemed'])) {
		echo '<em>'
			.__(
				'This voucher has not yet been redeemed. To redeem this voucher,'
				.' please hand it in to the retailer with your purchase.'
			)
			.'</em>';
	}
	else {
		echo '<p class="warning">'
			.__('Warning: This voucher has already been redeemed.').'</p>';
	}
	if (!Core_isAdmin()) {
		echo '<br/><br/><br/>'
			.__(
				'If you are the retailer, please <a href="/ww.admin/">log in</a>,'
				.' then scan the QR code again.'
			);
	}
	else {
		echo '<br/><br/><br/><a href="/a/p=online-store/f=adminRedeemVoucher/'
			.'oid='.$oid.'/pid='.$pid.'">'.__('Mark this voucher as redeemed.').'</a>';
	}
	Core_quit();
}

// }
// { OnlineStore_checkVoucher

/**
	* check a voucher to see if it's valid
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_checkVoucher($params) {
	require_once dirname(__FILE__).'/frontend/voucher-libs.php';
	if (!isset($params['code']) || !isset($params['email'])) {
		return array(
			'error'=>__('Invalid or missing parameters')
		);
	}
	$valid=OnlineStore_voucherCheckValidity($params['code'], $params['email']);
	if ($valid['error']) {
		return $valid;
	}
	else {
		return array('ok'=>1);
	}
}

// }
// { OnlineStore_getCountries

/**
	* get list of countries selected for the checkout
	*
	* @return array of countries
	*/
function OnlineStore_getCountries() {
	$page_id=(int)$_REQUEST['page_id'];
	$countries=json_decode(
		dbOne(
			'select value from page_vars where page_id='.$page_id
			.' and name="online-store-countries"', 'value'
		)
	);
	$c=array();
	foreach ($countries as $k=>$v) {
		$c[]=$k;
	}
	return $c;
}

// }
// { OnlineStore_getQrCode

/**
	* output a QR code for a voucher
	*
	* @return null
	*/
function OnlineStore_getQrCode() {
	require_once SCRIPTBASE.'/ww.incs/phpqrcode.php';
	$url=base64_decode($_REQUEST['b64']);
	$fname=USERBASE.'/ww.cache/online-store/qr'.md5($url);
	if (!file_exists($fname)) {
		@mkdir(USERBASE.'/ww.cache/online-store');
		QRcode::png(
			$url,
			$fname
		);
	}
	header('Content-type: image/png');
	header('Cache-Control: max-age=2592000, public');
	header('Expires-Active: On');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	header('Content-Length: ' . filesize($fname));
	readfile($fname);
	Core_quit();
}

// }
// { OnlineStore_invoicePdf

/**
	* get a PDF version of the invoice
	*
	* @return null
	*/
function OnlineStore_invoicePdf() {
	$id=(int)$_REQUEST['id'];
	$order=dbRow(
		'select invoice, meta from online_store_orders where id='.$id
		.' and user_id='.$_SESSION['userdata']['id']
	);
	if (!$order) {
		Core_quit();
	}
	$inv=$order['invoice'];
	// { check if it's already stored as a PDF
	$meta=json_decode($order['meta'], true);
	if (isset($meta['invoice-type']) && $meta['invoice-type']=='pdf') {
		$pdf=base64_decode($inv);
		header('Content-type: application/pdf');
		echo $pdf;
		Core_quit();
	}
	// }
	// { else generate a PDF and output it
	$pdfFile=USERBASE.'/ww.cache/online-store/invoice-pdf-'.$id;
	if (!file_exists($pdfFile)) {
		$html=OnlineStore_invoiceGet($id);
		require_once $_SERVER['DOCUMENT_ROOT']
			.'/ww.incs/dompdf/dompdf_config.inc.php';
		$dompdf=new DOMPDF();
		$dompdf->set_base_path($_SERVER['DOCUMENT_ROOT']);
		$dompdf->load_html(utf8_decode(str_replace('€', '&euro;', $html)), 'UTF-8');
		$dompdf->set_paper('a4');
		$dompdf->render();
		file_put_contents($pdfFile, $dompdf->output());
	}
	header('Content-type: application/pdf');
	$fp=fopen($pdfFile, 'r');
	fpassthru($fp);
	fclose($fp);
	Core_quit();
	// }
}

// }
// { OnlineStore_invoiceGet

/**
	* retrieve an invoice owned by the user
	*
	* @param int $id ID of the invoice
	*
	* @return string
	*/
function OnlineStore_invoiceGet($id) {
	$inv=dbOne(
		'select invoice from online_store_orders where id='.$id.' and user_id='
		.$_SESSION['userdata']['id'], 'invoice'
	);
	if (strpos($inv, '<body')===false) {
		$inv='<body>'.$inv.'</body>';
	}
	if (isset($_REQUEST['print'])) {
		$inv=str_replace('<body', '<body onload="window.print()"', $inv);
	}
	return $inv;
}

// }
// { OnlineStore_listSavedLists

/**
	* shopping lists
	*
	* @param array $params parameters
	*
	* @return array shopping list names
	*/
function OnlineStore_listSavedLists($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	$names=array();
	$rs=dbAll(
		'select name from online_store_lists where user_id='
		.$_SESSION['userdata']['id'].' order by name'
	);
	foreach ($rs as $r) {
		$names[]=$r['name'];
	}
	return array('names'=>$names);
}

// }
// { OnlineStore_loadSavedList

/**
	* save a shopping list
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_loadSavedList($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	if (!@$params['name']) {
		return array('error'=>'no list name supplied');
	}
	
	$data=dbOne(
		'select details from online_store_lists where '
		.' name="'.addslashes($params['name']).'" and user_id='
		.$_SESSION['userdata']['id'], 'details'
	);
	if (!$data) {
		return array('error'=>'no such list exists');
	}
	$_SESSION['online-store']=json_decode($data, true);
	
	return array('success'=>1);
}

// }
// { OnlineStore_pandpGetList

/**
	* get list of post and packaging methods
	*
	* @return array of pandp methods
	*/
function OnlineStore_pandpGetList() {
	$page_id=(int)$_REQUEST['page_id'];
	$pandp=json_decode(
		dbOne(
			'select value from page_vars where page_id='.$page_id
			.' and name="online_stores_postage"',
			'value'
		)
	);
	$c=array();
	foreach ($pandp as $k=>$v) {
		$c[]=$v->name;
	}
	return $c;
}

// }
// { OnlineStore_paymentTypesList

/**
	* get list of payment types accepted by a checkout
	*
	* @return array of payment types
	*/
function OnlineStore_paymentTypesList() {
	$page_id=(int)@$_REQUEST['page_id'];
	if ($page_id) {
		$page=Page::getInstance($page_id);
		$page->initValues();
	}
	else {
		$page=@$GLOBALS['PAGEDATA'];
		if ($page->type!='online-store') {
			$page=Page::getInstanceByType('online-store');
			if (!$page) {
				return array(
					'error'=>__('No online-store page created')
				);
			}
			$page->initValues();
		}
	}
	// { build list of payment methods
	$arr=array();
	if (@$page->vars['online_stores_quickpay_merchantid']) {
		$arr['QuickPay']=__('Credit Card');
	}
	if (@$page->vars['online_stores_realex_sharedsecret']) {
		$arr['Realex']=__('Credit Card');
	}
	if (@$page->vars['online_stores_paypal_address']) {
		$arr['PayPal']=__('PayPal');
	}
	if (@$page->vars['online_stores_bank_transfer_account_number']) {
		$arr['Bank Transfer']=__('Bank Transfer');
	}
	// }
	if (!count($arr)) {
		return array(
			// TODO: translation needed
			'error'=>'No payment methods have been defined.'
		);
	}
	return $arr;
}

// }
// { OnlineStore_saveSavedList

/**
	* save a shopping list
	*
	* @param array $params parameters
	*
	* @return array success status
	*/
function OnlineStore_saveSavedList($params) {
	if (!@$_SESSION['userdata']['id']) {
		return array('error'=>'you are not logged in');
	}
	if (!@$params['name']) {
		return array('error'=>'no list name supplied');
	}
	
	$data=json_encode($_SESSION['online-store']);
	dbQuery(
		'delete from online_store_lists where name="'.addslashes($params['name'])
		.'" and user_id='.$_SESSION['userdata']['id']
	);
	dbQuery(
		'insert into online_store_lists set name="'.addslashes($params['name'])
		.'",user_id='.$_SESSION['userdata']['id'].',details="'
		.addslashes($data).'"'
	);
	return array('success'=>1);
}

// }
// { OnlineStore_userRegister

/**
	* register a user in the checkout, without email validation
	*
	* @return array status
	*/
function OnlineStore_userRegister() {
	$email=$_REQUEST['email'];
	if (!isset($_SESSION['privacy'])) {
		$_SESSION['privacy']=array();
	}
	$_SESSION['privacy']['registration']=array(
		'token'=> 'token',
		'custom'=> array(),
		'email'=>$email
	);
	$_REQUEST['token']='token';
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-funcs.php';
	return Core_register();
}

// }
