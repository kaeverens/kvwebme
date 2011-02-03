<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$name=$_REQUEST['name'];
$id=(int)$_REQUEST['id'];
$phone=$_REQUEST['phone'];

if(!$name || $name=='[insert subscriber name]'){
	echo '{"err":1}';
	exit;
}
if(preg_replace('/[^0-9]/','',$phone)!=$phone
	|| !preg_match('/^44|^353/',$phone)
){
	echo '{"err":2}';
	exit;
}

if($id<1){
	dbQuery('insert into sms_subscribers (name,phone,date_created) values("'.addslashes($name).'","'.$phone.'",now())');
}
else{
	dbQuery('update sms_subscribers set name="'.addslashes($name).'",phone="'.$phone.'" where id='.$id);
}

echo '{"err":0}';
