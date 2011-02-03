<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$name=$_REQUEST['name'];
$phone=$_REQUEST['phone'];

if(!$name || $name=='[insert subscriber name]'){
	echo '{"err":1,"errmsg":"no name provided"}';
	exit;
}
if(preg_replace('/[^0-9]/','',$phone)!=$phone
	|| !preg_match('/^44|^353/',$phone)
){
	echo '{"err":2,"errmsg":"incorrect number format"}';
	exit;
}

$sid=sms_getSubscriberId($phone,$name);
if(!$sid){
	echo '{"err":2,"errmsg":"incorrect number format"}';
	exit;
}

$ids=explode(',',$_REQUEST['ids']);
foreach($ids as $aid){
	sms_subscribeToAddressbook($sid,(int)$aid);
}

echo '{"err":0}';
