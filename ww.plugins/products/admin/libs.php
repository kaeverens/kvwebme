<?php
function products_categories_get_data($id){
	$ps=dbAll('select product_id from products_categories_products where category_id='.$id);
	$products=array();
	$pageid= dbOne(
		'select page_id 
		from page_vars 
		where name="products_category_to_show" and value='.$id,
		'page_id'
	);
	foreach($ps as $p)$products[]=$p['product_id'];
	$data=array(
		'attrs'=>dbRow('select id,associated_colour,name,enabled,parent_id from products_categories where id='.$id),
		'products'=>$products,
	);
	if (isset($pageid)) {
		$page= Page::getInstance($pageid);
		if ($page) {
			$url= $page->getRelativeUrl();
			$data['page']= $url;
		}
	}
	return $data;
}
