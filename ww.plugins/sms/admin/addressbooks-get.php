<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$id=(int)$_REQUEST['id'];
$r=dbRow('select id,name,subscribers from sms_addressbooks where id='.$id);
$r['subscribers']=json_decode($r['subscribers']);
echo json_encode($r);
