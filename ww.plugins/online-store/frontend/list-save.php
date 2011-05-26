<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

header('Content-type: text/json');

if (!@$_SESSION['userdata']['id']) {
	die('{"error":"you are not logged in"}');
}
if (!@$_REQUEST['name']) {
	die('{"error":"no list name supplied"}');
}

$data=json_encode($_SESSION['online-store']);
dbQuery(
	'delete from online_store_lists where name="'.addslashes($_REQUEST['name'])
	.'" and user_id='.$_SESSION['userdata']['id']
);
dbQuery(
	'insert into online_store_lists set name="'.addslashes($_REQUEST['name'])
	.'",user_id='.$_SESSION['userdata']['id'].',details="'
	.addslashes($data).'"'
);

echo '{"success":1}';
