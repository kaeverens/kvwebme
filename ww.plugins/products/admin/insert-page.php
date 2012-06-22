<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}
$name= addslashes($_REQUEST['name']);
$parent= (int)$_REQUEST['parent'];
$what= (int)$_REQUEST['what'];
$id= (int)$_REQUEST['id'];
$i = 2;
while (dbOne('select id from pages where name=\''.$name.'\'', 'id')) {
	$name.=$i;
	$i++;
}
dbQuery(
	"insert into pages(name, cdate, type, parent, special, associated_date, alias) 
	values('$name', now(), 'products', '$parent', 0, now(), '$name')"
);
$pageid= (int)dbOne('select last_insert_id() as id', 'id');
$page= Page::getInstance($pageid);
$url= $page->getRelativeUrl();

dbQuery(
	"insert into page_vars(page_id, name, value) 
	values('$pageid', 'products_what_to_show', '$what')"
);
switch ($what) {
	case 3: // { Create a page to show a product
		dbQuery(
			"insert into page_vars(page_id, name, value)
			values('$pageid', 'products_product_to_show', '$id')"
		);
		dbQuery(
			"insert into page_vars(page_id, name, value)
			values('$pageid','products_category_to_show', 0)"	
		);
	break; // }
	case 2: // { Create a category page
		dbQuery(
			"insert into page_vars(page_id, name, value)
			values('$pageid', 'products_category_to_show', '$id')"
		);
		dbQuery(
			"insert into page_vars(page_id, name, value)
			values('$pageid', 'products_product_to_show', 0)"
		);
	break; // }
	default:
	break;
}
dbQuery(
	"insert into page_vars(page_id, name, value)
	values('$pageid', 'products_type_to_show', 0)"
);
dbQuery(
	"insert into page_vars(page_id, name, value)
	values('$pageid', 'products_order_direction', 0)"
);
dbQuery(
	"insert into page_vars(page_id, name, value)
	values('$pageid', 'products_add_a_search_box', 0)"
);
dbQuery(
	"insert into page_vars(page_id, name, value)
	values('$pageid', 'products_per_page', 0)"
);
if ($what==2) {
	$product
		= dbOne(
			'select product_id 
			from products_categories_products 
			where category_id='.$id,
			'product_id'
		);
}
else {
	$product= $id;
}
if ($product) {
	$datafields
		= dbOne
		(
			'select data_fields from products where id='.$product, 
			'data_fields'
		);
	if ($datafields) {
		$data=json_decode($datafields);
		if ($data!==false) {
			$firstField=$data->n;
			dbQuery(
				"insert into page_vars(page_id, name, value)
				values('$pageid', 'products_order_by', '".addslashes($firstField)."')"
			);
		}
	}
}

if (dbOne("select name from pages where id=$pageid", 'name')==stripslashes($name)) {
	$message=__('Page Created');
	$status= 1;
}
else {
	$message=__('Failed to create page');
	$status= 0;
}
echo '{
		"status":'.$status.','.
		'"message":"'.$message.'",'.
		'"what":'.$what.','.
		'"url":"'.$url.'"'.
	'}';

Core_cacheClear();
