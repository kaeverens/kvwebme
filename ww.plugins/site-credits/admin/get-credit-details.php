<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

header('Content-type: text/json');
$rs=dbAll('select * from sitecredits_options');
$options=array();
foreach ($rs as $k=>$v) {
	$options[$v['name']]=$v['value'];
}
if (!(@$options['payment-recipient'])) {
	$cr=DistConfig::get('credits-email');
	$options['payment-recipient']=$cr;
	dbQuery(
		'insert into sitecredits_options values("payment-recipient", "'.$cr.'")'
	);
}
if (!(@$options['currency'])) {
	$options['currency']='EUR';
	$options['currency-symbol']='€';
}
if (!(@$options['credit-costs'])) {
	$options['credit-costs']='['
//		.'[50,1],[150,0.9],[500,0.81],[1500,0.73],[5000,0.64]'
		.'[5000000,1]'
		.']';
}
$options['credit-costs']=json_decode($options['credit-costs']);
echo json_encode($options);
