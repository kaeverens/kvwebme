<?php
/**
	* generate a button for QuickPay payments
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $DBVARS;
$http=((empty($_SERVER['HTTPS'])||$_SERVER['HTTPS']=='off')
	?'http://'
	:'https://');
$callbackurl=$http.$_SERVER['HTTP_HOST'].'/ww.plugins/online-store/verify/'
	.'quickpay.php';

// { redirect URL for cancelled purchases
$canc=Page::getInstance(
	$PAGEDATA->vars['online_store_quickpay_redirect_failed']
);
// }
// { redirect URL (for successful purchases
$cont=Page::getInstance($PAGEDATA->vars['online_store_quickpay_redirect_to']);
if ($cont) {
	$cont_url=$cont->getAbsoluteURL();
}
else {
	$rp=Page::getInstanceByType('privacy');
	if ($rp) {
		$cont_url=$rp->getAbsoluteUrl().'?onlinestore_iid='.$id;
	}
	else {
		$cont_url='http://'.$_SERVER['HTTP_HOST'].'/';
	}
}
// }

$fields = array(
	'protocol'    => 4,
	'msgtype'     => 'authorize',
	'merchant'    => $PAGEDATA->vars['online_stores_quickpay_merchantid'],
	'language'    => 'en',
	'ordernumber' => str_pad($id, 8, '0', STR_PAD_LEFT),
	'amount'      => (int)($total*100),
	'currency'    => $DBVARS['online_store_currency'],
	'continueurl' => $cont_url,
	'cancelurl'   => $canc->getAbsoluteURL(),
	'callbackurl' => $callbackurl,
	'autocapture' => $PAGEDATA->vars['online_stores_quickpay_autocapture'],
	'cardtypelock'=> '',
	'group'       => 0,
	'splitpayment'=> 0
);

// { calculate required MD5 checksum
$md5_word = '';
foreach ($fields as $key => $value) {
	$md5_word .= $value;
}
$md5_word .= $PAGEDATA->vars['online_stores_quickpay_secret'];
$fields['md5check'] = md5($md5_word);
// }

$html='<form style="display:none" id="online-store-quickpay" method="post" action="'
	.'https://secure.quickpay.dk/form/">';
foreach ($fields as $k=>$v) {
	$html.='<input type="hidden" name="'.htmlspecialchars($k).'" '
		.'value="'.htmlspecialchars($v).'"/>';
}
$html.='<input type="submit" value="'.htmlspecialchars(__('Proceed to Payment')).'"/></form>'
	.'<script defer="defer">document.getElementById("online-store-quickpay")'
	.'.submit()</script>';

echo $html;
Core_quit();
