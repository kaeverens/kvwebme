<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$url='http://textr.mobi/api.php?a=activate'
	.'&email='.urlencode($DBVARS['sms_email'])
	.'&activation='.urlencode($_REQUEST['key']);

$res=file_get_contents($url);
if($res===false){
	echo '{"status":0,"error":"failed to contact textr.mobi. please wait a short while and try again."}';
	exit;
}

echo $res;
