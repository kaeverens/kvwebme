<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['email'])
	|| !filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)
	|| !isset($_REQUEST['pass'])
	|| !$_REQUEST['pass']
){
	echo '{"status":0,"error":"email and password must be provided"}';
	exit;
}

$url='http://textr.mobi/api.php?a=subscribe'
	.'&email='.urlencode($_REQUEST['email'])
	.'&password='.urlencode($_REQUEST['pass']);

$res=file_get_contents($url);
if($res===false){
	echo '{"status":0,"error":"failed to contact textr.mobi. please wait a short while and try again."}';
	exit;
}

$json=json_decode($res);
if($json->status){ // successful subscription. record details
	$DBVARS['sms_email']=$_REQUEST['email'];
	$DBVARS['sms_password']=$_REQUEST['pass'];
	config_rewrite();
}

echo $res;
