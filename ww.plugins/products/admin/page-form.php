<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
$c.= '<div class="tabs">';
$c.= '<ul>';
$c.= '<li><a href="#products-main">Main Details</a></li>';
$c.= '<li><a href="#products-header">Header</a></li>';
$c.= '<li><a href="#products-footer">Footer</a></li>';
$c.= '</ul>';
// { main details
$c.= '<div id="products-main"><table class="tab-table">';
// { what should be shown
$c.= '<tr><th>What to show</th>';
$c.= '<td><select id="products_what_to_show" name="page_vars[products_what_to_show]">';
$arr
	=array(
		'All products',
		'All products from a specified type',
		'All products from a specified category',
		'A specific product'
	);
if (!isset($vars['products_what_to_show']))$vars['products_what_to_show']=0;
foreach($arr as $k=>$r){
	$c.= '<option value="'.$k.'"';
	if($k==$vars['products_what_to_show'])$c.= ' selected="selected"';
	$c.= '>'.htmlspecialchars($r).'</option>';
}
$c.= '</select></td></tr>';
// }
// { type names
$c.= '<tr id="products_what_to_show_1"><th>Which product type to show</th><td>';
$rs=dbAll('select id,name from products_types order by name');
if($rs===false || !count($rs)){
	$c.= '<p><strong>no types exist.</strong>'
		.'<a href="/ww.admin/plugin.php?_plugin=products&_page=types">'
			.'click here to create a product type</a>.</p>';
}
else{
	$c.='<select name="page_vars[products_type_to_show]"><option value="0">'
		.'-- choose -- </option>';
	foreach($rs as $r){
		$c.='<option value="'.$r['id'].'"';
		if (isset($vars['products_type_to_show']) && $r['id']==$vars['products_type_to_show']) {
			$c.=' selected="selected"';
		}
		$c.='>'.htmlspecialchars($r['name']).'</option>';
	}
	$c.='</select>';
}
$c.='</td></tr>';
// }
// { category names
$c.='<tr id="products_what_to_show_2"><th>Which category to show</th><td>';
$rs=dbAll('select id,name from products_categories order by name');
if($rs===false || !count($rs)){
	$c.='<p><strong>no categories exist.</strong> <a href="/ww.admin/plugin.php?_plugin=products&_page=categories">click here to create a product category</a>.</p>';
}
else{
	$c.='<select name="page_vars[products_category_to_show]"><option value="0"> -- choose -- </option>';
	foreach($rs as $r){
		$c.='<option value="'.$r['id'].'"';
		if (isset($vars['products_category_to_show']) && $r['id']==$vars['products_category_to_show']) {
			$c.=' selected="selected"';
		}
		$c.='>'.htmlspecialchars($r['name']).'</option>';
	}
	$c.='</select>';
}

$c.='</td></tr>';
// }
// { product names
$c.='<tr id="products_what_to_show_3"><th>Which product to show</th><td>';
$rs=dbAll('select id,name from products order by name');
if($rs===false || !count($rs)){
	$c.='<p><strong>no products exist.</strong> <a href="/ww.admin/plugin.php?_plugin=products&_page=products">click here to create a product</a>.</p>';
}
else{
	$c.='<select name="page_vars[products_product_to_show]"><option value="0"> -- choose -- </option>';
	foreach($rs as $r){
		$c.='<option value="'.$r['id'].'"';
		if (isset($vars['products_product_to_show']) && $r['id']==$vars['products_product_to_show']) {
			$c.=' selected="selected"';
		}
		$c.='>'.htmlspecialchars($r['name']).'</option>';
	}
	$c.='</select>';
}
$c.='</td></tr>';
// }
// { search box
$c.='<tr id="products_search"><th>Add a search-box</th><td><select name="page_vars[products_add_a_search_box]">';
$c.='<option value="0">No</option><option value="1"';
if(isset($vars['products_add_a_search_box']) && $vars['products_add_a_search_box']=='1')$c.=' selected="selected"';
$c.='>Yes</option></select></td></tr>';
// }
// { order by
$c.='<tr id="products_order_by"><th>Order By</th><td><select id="products_order_by_select" name="page_vars[products_order_by]">';
if(!isset($vars['products_order_by'])){
	$fs=json_decode(dbOne('select data_fields from products_types limit 1','data_fields'));
	$c.='<option>'.htmlspecialchars($fs[0]->n).'</option>';
}
else $c.='<option>'.htmlspecialchars($vars['products_order_by']).'</option>';
$c.='</select>';
$c.='<select name="page_vars[products_order_direction]">';
$c.='<option value="0">Ascending (A-Z)</option><option value="1"';
if(isset($vars['products_order_direction']) && $vars['products_order_direction']=='1')$c.=' selected="selected"';
$c.='>Descending (Z-A)</option></select></td></tr>';
// }
// { template to use for multiple products
$c.= '<tr id="products-show-multiple-with-row"><th>Show multiple products using</th>';
$c.= '<td><select id="products_show_multiple_with" name="page_vars[products_show_multiple_with]">';
$arr
	=array(
		'product type template',
		'horizontal table (headers on top)',
		'vertical table (headers on left)'
	);
if (!isset($vars['products_show_multiple_with']))$vars['products_show_multiple_with']=0;
foreach($arr as $k=>$r){
	$c.= '<option value="'.$k.'"';
	if($k==$vars['products_show_multiple_with'])$c.= ' selected="selected"';
	$c.= '>'.htmlspecialchars($r).'</option>';
}
$c.= '</select></td></tr>';
// }
// { products per page
$c.= '<tr id="products_per_page"><th>Products per page</th><td>';
$c.= '<input name="page_vars[products_per_page]" class="small" value="';
$i = isset($vars['products_per_page'])?(int)$vars['products_per_page']:0;
if ($i<0)$i=0;
$c.= $i.'" /></td></tr>';
// }
// { add export button
$c.='<tr id="products_export"><th>Add an Export button</th><td><select name="page_vars[products_add_export_button]">';
$c.='<option value="0">No</option><option value="1"';
if(isset($vars['products_add_export_button']) && $vars['products_add_export_button']=='1')$c.=' selected="selected"';
$c.='>Yes</option></select></td></tr>';
// }
$c.= '</table></div>';
// }
// { header
$c.= '<div id="products-header">';
$c.= '<p>Text to be shown above the product/product list</p>';
$c.= ckeditor('body',$page['body']);
$c.= '</div>';
// }
// { footer
$c.= '<div id="products-footer">';
$c.= '<p>Text to be shown below the product/product list</p>';
$c.= ckeditor('page_vars[footer]',isset($vars['footer'])?$vars['footer']:'');
$c.= '</div>';
// }
$c.= '</div>';
$c.= '<script type="text/javascript" src="/ww.plugins/products/admin/page-form.js"></script>';
