<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');
require SCRIPTBASE.'ww.plugins/sms/admin/libs.php';

$aid=(int)$_REQUEST['to'];
$msg=$_REQUEST['msg'];
if(!$msg)exit;
if(preg_replace('/a-zA-Z0-9 !_\-.,:\'"/','',$msg)!=$msg)exit;

$tos=array();
$to_names=array();
$subs=dbOne('select subscribers from sms_addressbooks where id='.$aid.' limit 1','subscribers');
$subs=dbAll('select name,phone from sms_subscribers where id in ('.preg_replace('/[^0-9,]/','',$subs).')');
foreach($subs as $sub){
	$tos[]=$sub['phone'];
	$to_names[]=preg_replace('/[^a-zA-Z0-9 \-.\']/','',$sub['name']);;
}

$ret=SMS_callApi('send-bulk','&to='.join(',',$tos).'&message='.urlencode($msg).'&names='.join(',',$to_names));
echo json_encode($ret);
