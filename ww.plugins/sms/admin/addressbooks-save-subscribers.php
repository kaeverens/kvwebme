<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

$id=(int)$_REQUEST['aid'];
$subs=preg_replace('/[^0-9,]/','',$_REQUEST['subscribers']);
dbQuery('update sms_addressbooks set subscribers="['.$subs.']" where id='.$id);

echo '{"ok":1}';
