<?php

/**
  * Gets the new json for the type
  *
  * @category   ProductsPlugin
  * @package    WebworksWebme
  * @subpackage ProductsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2.0
  * @link       www.webworks.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$typeID = $_REQUEST['type'];
$productID = $_REQUEST['product'];
if (!is_admin()) {
	die('You do not have permission to do this');
}
if (!is_numeric($typeID)||!is_numeric($productID)) {
	exit('Invalid arguments');
}
if (!dbOne('select id from products_types where id = '.$typeID, 'id')) {
	echo '{"status":0, "message":"Could not find this type"}';
}
else {
	$data = array();
	$typeData 
		 = dbRow(
			'select data_fields, is_for_sale '
			.'from products_types '
			.'where id = '.$typeID
		);
	$typeFields = json_decode($typeData['data_fields']);
	$data['type'] = $typeFields;
	$data['isForSale'] = $typeData['is_for_sale'];
	if ($productID != 0) {
		$product 
			= dbRow(
				'select data_fields, product_type_id 
				from products where id = '.$productID
			);
		$productFields = json_decode($product['data_fields']);
		$oldType 
			= dbOne(
				'select data_fields 
				from products_types 
				where id = '.$product['product_type_id'],
				'data_fields'
			);
		$oldType = json_decode($oldType);
		$data['product'] = $productFields;
		$data['oldType'] = $oldType;
	}
	echo json_encode($data);
}
