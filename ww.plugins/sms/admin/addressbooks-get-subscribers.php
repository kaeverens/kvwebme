<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$id=(int)$_REQUEST['id'];
$r=dbRow('select subscribers from sms_addressbooks where id='.$id);
$subs=explode(',',str_replace(array('[',']','"'),'',$r['subscribers']));

$rs=dbAll('select id,name,phone from sms_subscribers order by name');
foreach($rs as $k=>$r){
	if(in_array($r['id'],$subs))$rs[$k]['c']=1;
}
echo json_encode($rs);
