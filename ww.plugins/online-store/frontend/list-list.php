<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

header('Content-type: text/json');

if (!@$_SESSION['userdata']['id']) {
	die('{"error":"you are not logged in"}');
}

$names=array();
$rs=dbAll(
	'select name from online_store_lists where user_id='
	.$_SESSION['userdata']['id'].' order by name'
);
foreach ($rs as $r) {
	$names[]=$r['name'];
}

echo json_encode(array(
	'names'=>$names
));
