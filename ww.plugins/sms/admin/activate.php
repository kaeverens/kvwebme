<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

$url='http://textr.mobi/api.php?a=activate'
	.'&email='.urlencode($DBVARS['sms_email'])
	.'&activation='.urlencode($_REQUEST['key']);

$res=file_get_contents($url);
if($res===false){
	Core_quit(
		'{"status":0,"error":"failed to contact textr.mobi. please wait a short'
		.' while and try again."}'
	);
}

echo $res;
