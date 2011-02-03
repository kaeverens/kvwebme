<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$fields=array();
$filter='';
if($_REQUEST['other_GET_params']){
	if(is_numeric($_REQUEST['other_GET_params'])){ // product type
		$filter=' where id='.(int)$_REQUEST['other_GET_params'];
	}
	else if(strpos($_REQUEST['other_GET_params'],'c')===0){
		$cat=(int)str_replace('c','',$_REQUEST['other_GET_params']);
		if($cat==0) {
			$rs=dbAll('select distinct product_type_id from products');
		}
		else {
			$rs=dbAll('select product_id from products_categories_products where category_id='.$cat);
			$arr=array();
			foreach($rs as $r)$arr[]=$r['product_id'];
			if(!count($arr))exit;
			$rs=dbAll('select distinct product_type_id from products where id in ('.join(',',$arr).')');
		}
		$arr=array();
		foreach($rs as $r)$arr[]=$r['product_type_id'];
		if(!count($arr))exit;
		$filter=' where id in ('.join(',',$arr).')';
	}
}
$rs=dbAll('select data_fields from products_types'.$filter);
foreach($rs as $r){
	$fs=json_decode($r['data_fields']);
	foreach($fs as $f)$fields[]=$f->n;
}
$fields=array_unique($fields);
asort($fields);
foreach ($fields as $field) {
	echo '<option';
	if ($field==$vars['products_order_by']) {
		echo ' selected="selected"';
	}
	echo '>', htmlspecialchars($field), '</option>';
}
