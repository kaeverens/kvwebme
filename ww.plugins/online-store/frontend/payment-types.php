<?php

$page=$GLOBALS['PAGEDATA'];
if($page->type!='online-store'){
	$page=Page::getInstanceByType('online-store');
	if(!$page){
		$c='<em>No <strong>online-store</strong> page created.</em>';
		return;
	}
	$page->initValues();
}

// { build list of payment methods
$arr=array();
if (@$page->vars['online_stores_realex_sharedsecret']) {
	$arr['Realex']='Credit Card';
}
if (@$page->vars['online_stores_paypal_address']) {
	$arr['PayPal']='PayPal';
}
if (@$page->vars['online_stores_bank_transfer_account_number']) {
	$arr['Bank Transfer']='Bank Transfer';
}
// }

if(!count($arr)){
	$c.='<em>No payment methods have been defined.</em>';
	return;
}

$c='<select id="payment_method_type" name="_payment_method_type">';
foreach($arr as $n=>$v){
	$c.='<option value="'.$n.'"';
	if(
		isset($_REQUEST['_payment_method_type'])
		&& $_REQUEST['_payment_method_type']==$n
	){
		$c.=' selected="selected"';
	}
	$c.='>'.$v.'</option>';
}
$c.='</select>';
