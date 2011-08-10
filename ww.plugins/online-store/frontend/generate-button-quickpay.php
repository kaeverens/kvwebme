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

$cont=Page::getInstance($PAGEDATA->vars['online_store_quickpay_redirect_to']);
$canc=Page::getInstance($PAGEDATA->vars['online_store_quickpay_redirect_failed']);

$fields = array(
	'protocol'    => 4,
	'msgtype'     => 'authorize',
	'merchant'    => $PAGEDATA->vars['online_stores_quickpay_merchantid'],
	'language'    => 'en',
	'ordernumber' => str_pad($id, 8, '0', STR_PAD_LEFT),
	'amount'      => $total * 100,
	'currency'    => $DBVARS['online_store_currency'],
	'continueurl' => $cont->getAbsoluteURL(),
	'cancelurl'   => $canc->getAbsoluteURL(),
	'callbackurl' => $callbackurl,
	'autocapture' => $PAGEDATA->vars['online_stores_quickpay_autocapture'],
	'cardtypelock'=> '',
	'group'       => 0,
	'splitpayment'=> 0
);

// calculate required MD5 checksum
$md5_word = '';
foreach ($fields as $key => $value) {
	$md5_word .= $value;
}
$md5_word .= $PAGEDATA->vars['online_stores_quickpay_secret'];
$fields['md5check'] = md5($md5_word);

$html='<form id="online-store-quickpay" method="post" action="'
	.'https://secure.quickpay.dk/form/">';
foreach ($fields as $k=>$v) {
	$html.='<input type="hidden" name="'.htmlspecialchars($k).'" '
		.'value="'.htmlspecialchars($v).'"/>';
}
$html.='<input type="submit" value="Proceed to Payment"/></form>'
	.'<script>$("#online-store-quickpay").submit()</script>';
