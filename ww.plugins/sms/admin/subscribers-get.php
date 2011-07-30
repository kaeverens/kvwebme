<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

$id=(int)$_REQUEST['id'];

$rs=dbRow('select * from sms_subscribers where id='.$id);
echo json_encode($rs);
