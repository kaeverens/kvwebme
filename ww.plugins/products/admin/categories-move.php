<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!is_admin()) {
	die('access denied');
}

$id=(int)$_REQUEST['id'];
$p_id=(int)$_REQUEST['parent_id'];
$order=explode(',', $_REQUEST['order']);

dbQuery('update products_categories set parent_id='.$p_id.' where id='.$id);
for ($i=0;$i<count($order);++$i) {
	$id=(int)$order[$i];
	dbQuery('update products_categories set sortNum='.$i.' where id='.$id);
}

echo 'ok';
