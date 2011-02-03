<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$id=(int)$_REQUEST['id'];
dbQuery('delete from sms_addressbooks where id='.$id);
echo '{"err":0,id:'.$id.'}';
