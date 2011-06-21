<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

$id=(int)$_REQUEST['id'];

header('Content-type: text/json');
$r=dbRow('select * from online_store_orders where id='.$id);
if (!$r || !$r['items']) {
	echo '{"error":"no such order"}';
}
$items=array();
foreach (json_decode($r['items'], true) as $item) {
	$items[]=array(
		'id'=>$item['id'],
		'name'=>(@$item['name']?$item['name']:$item['short_desc']),
		'amt'=>$item['amt']
	);
}
echo json_encode($items);
