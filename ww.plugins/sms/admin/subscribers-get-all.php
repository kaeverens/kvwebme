<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

$rs=dbAll('select id,name from sms_subscribers order by name');
echo json_encode($rs);
