<?php
/**
	* retrieve product data for a DataTables display
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

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

echo json_encode(
	array(
		'iTotalRecords'=>$total_records,
		'iTotalDisplayRecords'=>$total_records,
		'aaData'=>$returned_products
	)
);
