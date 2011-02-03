<?php
require 'datatable-libs.php';

$i=$_REQUEST['iDisplayStart'];
$finish=$_REQUEST['iDisplayStart']+$_REQUEST['iDisplayLength'];

for (; $i<$finish && $i<$total_records; ++$i) {
	$arr=array();
	$p=Product::getInstance($products->product_ids[$i]);
	foreach ($columns as $name) {
		$arr[]=$p->getString($name);
	}
	$returned_products[]=$arr;
}

echo json_encode(array(
	'iTotalRecords'=>$total_records,
	'iTotalDisplayRecords'=>$total_records,
	'aaData'=>$returned_products
));
