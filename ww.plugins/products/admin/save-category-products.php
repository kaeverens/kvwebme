<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id']))exit;

include 'libs.php';

dbQuery('delete from products_categories_products where category_id='.$_REQUEST['id']);
foreach($_REQUEST['s'] as $p){
	dbQuery('insert into products_categories_products set product_id='.((int)$p).',category_id='.$_REQUEST['id']);
}
cache_clear('products');

$data=products_categories_get_data($_REQUEST['id']);

echo json_encode($data);
