<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$name=$_REQUEST['name'];
$id=(int)$_REQUEST['id'];
$subscribers=$_REQUEST['subscribers'];

if(!$name || $name=='[insert addressbook name]' || preg_replace('/[0-9,]*/','',$subscribers)!=''){
	echo '{"err":1}';
	exit;
}

if($id<1){
	dbQuery('insert into sms_addressbooks (name,subscribers,date_created) values("'.addslashes($name).'","['.$subscribers.']",now())');
}
else{
	dbQuery('update sms_addressbooks set name="'.addslashes($name).'",subscribers="['.$subscribers.']" where id='.$id);
}

echo '{"err":0}';
