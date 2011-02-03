<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$product_id=(int)$_REQUEST['product_id'];
$image_id=(int)$_REQUEST['id'];

dbQuery('update products set image_default='.$image_id.' where id='.$product_id);

echo 'ok';
