<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

header('Content-type: text/json');

if (!@$_SESSION['userdata']['id']) {
	die('{"error":"you are not logged in"}');
}
if (!@$_REQUEST['name']) {
	die('{"error":"no list name supplied"}');
}

$data=dbOne(
	'select details from online_store_lists where '
	.' name="'.addslashes($_REQUEST['name']).'" and user_id='
	.$_SESSION['userdata']['id'], 'details'
);
if (!$data) {
	die('{"error":"no such list exists"}');
}
$_SESSION['online-store']=json_decode($data, true);

echo '{"success":1}';
