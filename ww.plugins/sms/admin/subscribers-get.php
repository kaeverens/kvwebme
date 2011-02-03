<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$id=(int)$_REQUEST['id'];

$rs=dbRow('select * from sms_subscribers where id='.$id);
echo json_encode($rs);
