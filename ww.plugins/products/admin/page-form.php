<?php
/**
	* form for editing a product page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$id=$page['id'];
$c='';
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
$c.='<p>To edit products, please <a href="/ww.admin/plugin.php?_plugin='
	.'products&amp;_page=products" target="_top">click here</a>.</p>';
$c.= '<div class="tabs">';
$c.= '<ul>';
$c.= '<li><a href="#products-main">Main Details</a></li>';
$c.= '<li><a href="#products-header">Header</a></li>';
$c.= '<li><a href="#products-footer">Footer</a></li>';
$c.= '</ul>';
// { main details
$c.= '<div id="products-main"><table class="tab-table">';
// { what should be shown
$c.='<tr><th>What to show</th>'
	.'<td><select id="products_what_to_show" '
	.'name="page_vars[products_what_to_show]">';
$arr=array(
	'All products',
	'All products from a specified type',
	'All products from a specified category',
	'A specific product'
);
if (!isset($vars['products_what_to_show'])) {
	$vars['products_what_to_show']=0;
}
foreach ($arr as $k=>$r) {
	$c.= '<option value="'.$k.'"';
	if ($k==$vars['products_what_to_show']) {
		$c.= ' selected="selected"';
	}
	$c.= '>'.htmlspecialchars($r).'</option>';
}
$c.= '</select></td></tr>';
// }
// { type names
$c.= '<tr id="products_what_to_show_1"><th>Which product type to show</th><td>';
$rs=dbAll('select id,name from products_types order by name');
if ($rs===false || !count($rs)) {
	$c.= '<p><strong>no types exist.</strong>'
		.'<a href="/ww.admin/plugin.php?_plugin=products&_page=types">'
			.'click here to create a product type</a>.</p>';
}
else {
	$c.='<select name="page_vars[products_type_to_show]"><option value="0">'
		.'-- choose -- </option>';
	foreach ($rs as $r) {
		$c.='<option value="'.$r['id'].'"';
		if (@$vars['products_type_to_show']==$r['id']) {
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
function showCategoriesRecursive($pid, $level, $sid) {
	$opts=array();
	$cs=dbAll(
		'select id,name from products_categories where parent_id='.$pid.' order by name'
	);
	foreach ($cs as $c) {
		$opt='<option value="'.$c['id'].'"';
		if ($c['id']==$sid) {
			$opt.=' selected="selected"';
		}
		$opt.='>'.str_repeat('&raquo;&nbsp;', $level)
			.htmlspecialchars(__FromJson($c['name'])).'</option>';
		$opts[]=$opt;
		$opts[]=showCategoriesRecursive($c['id'], $level+1, $sid);
	}
	return join('', $opts);
}
if ($rs===false || !count($rs)) {
	$c.='<p><strong>no categories exist.</strong> '
		.'<a href="/ww.admin/plugin.php?_plugin=products&amp;_page=categories">'
		.'click here to create a product category</a>.</p>';
}
else {
	$c.='<select name="page_vars[products_category_to_show]">'
		.'<option value="0"> -- choose -- </option>'
		.showCategoriesRecursive(0, 0, @$vars['products_category_to_show']);
	$c.='</select>';
}
$c.='</td></tr>';
// }
// { product names
$c.='<tr id="products_what_to_show_3"><th>Which product to show</th><td>';
$rs=dbAll('select id,name from products order by name');
if ($rs===false || !count($rs)) {
	$c.='<p><strong>no products exist.</strong> '
		.'<a href="/ww.admin/plugin.php?_plugin=products&amp;_page=products">'
		.'click here to create a product</a>.</p>';
}
else {
	$c.='<select name="page_vars[products_product_to_show]">'
		.'<option value="0"> -- choose -- </option>';
	foreach ($rs as $r) {
		$c.='<option value="'.$r['id'].'"';
		if (@$vars['products_product_to_show']==$r['id']) {
			$c.=' selected="selected"';
		}
		$c.='>'.htmlspecialchars($r['name']).'</option>';
	}
	$c.='</select>';
}
$c.='</td></tr>';
// }
// { search box
$c.='<tr id="products_search"><th>Add a search-box</th><td>'
	.'<select name="page_vars[products_add_a_search_box]">';
$c.='<option value="0">No</option><option value="1"';
if (@$vars['products_add_a_search_box']=='1') {
	$c.=' selected="selected"';
}
$c.='>Yes</option></select></td></tr>';
// }
// { order by
$c.='<tr id="products_order_by"><th>'.__('Order By').'</th><td>'
	.'<select id="products_order_by_select" name="page_vars[products_order_by]">';
if (!isset($vars['products_order_by'])) {
	$fs=dbOne(
		'select data_fields from products_types limit 1', 'data_fields'
	);
	if (!$fs) {
		$fs='[{"n":"description"}]';
	}
	$fs=json_decode($fs);
	$c.='<option>'.htmlspecialchars($fs[0]->n).'</option>';
}
else {
	$c.='<option>'.htmlspecialchars($vars['products_order_by']).'</option>';
}
$c.='</select>';
$c.='<select name="page_vars[products_order_direction]">';
$c.='<option value="0">Ascending (A-Z)</option><option value="1"';
if (@$vars['products_order_direction']=='1') {
	$c.=' selected="selected"';
}
$c.='>Descending (Z-A)</option>';
$c.='<option value="2"';
if (@$vars['products_order_direction']=='2') {
	$c.=' selected="selected"';
}
$c.='>Random </option></select>';

// }
// { template to use for multiple products
$c.= '<tr id="products-show-multiple-with-row">'
	.'<th>Show multiple products using</th>'
	.'<td><select id="products_show_multiple_with" '
	.'name="page_vars[products_show_multiple_with]">';
$arr=array(
	'0'=>'product type template',
	'4'=>'carousel',
	'3'=>'map view',
	'1'=>'horizontal table (headers on top)',
	'2'=>'vertical table (headers on left)'
);
if (!isset($vars['products_show_multiple_with'])) {
	$vars['products_show_multiple_with']=0;
}
foreach ($arr as $k=>$r) {
	$c.= '<option value="'.$k.'"';
	if ($k==$vars['products_show_multiple_with']) {
		$c.= ' selected="selected"';
	}
	$c.= '>'.htmlspecialchars($r).'</option>';
}
$c.= '</select></td></tr>';
// }
// { limits on amount shown
$c.= '<tr id="products_per_page"><th>'.__('Products per page').'</th><td>';
// { per page
$c.= '<input name="page_vars[products_per_page]" class="small" value="';
$i = isset($vars['products_per_page'])?(int)$vars['products_per_page']:0;
if ($i<0) {
	$i=0;
}
$c.= $i.'" />';
// }
// { min offset
$c.=' ('.__('min. offset')
	.' <input name="page_vars[products_per_page_offset_min]"'
	.' class="small" value="';
$i = isset($vars['products_per_page_offset_min'])
	?(int)$vars['products_per_page_offset_min']:0;
if ($i<0) {
	$i=0;
}
$c.= $i.'" />)';
// }
$c.='</td></tr>';
// }
// { add export button
$c.='<tr id="products_export"><th>Add an Export button</th><td>'
	.'<select name="page_vars[products_add_export_button]">';
$c.='<option value="0">No</option><option value="1"';
if (@$vars['products_add_export_button']=='1') {
	$c.=' selected="selected"';
}
$c.='>Yes</option></select></td></tr>';
// }
// { override page title (multiple products view)
$c.='<tr id="products-pagetitleoverride-multiple"><th>Page Title Override'
	.' (multiple products view)</th><td>'
	.'<input name="page_vars[products_pagetitleoverride_multiple]" value="'
	.htmlspecialchars(@$vars['products_pagetitleoverride_multiple'])
	.'"/></td></tr>';
// }
// { override page title (single page view)
$c.='<tr id="products-pagetitleoverride-single"><th>Page Title Override'
	.' (single product view)</th><td>'
	.'<input name="page_vars[products_pagetitleoverride_single]" value="'
	.htmlspecialchars(@$vars['products_pagetitleoverride_single'])
	.'"/></td></tr>';
// }
// { show sub-categories in navigation menu
$c.='<tr id="products_show_subcats_in_menu">'
	.'<th>Show sub-categories in menu</th><td>'
	.'<select name="page_vars[products_show_subcats_in_menu]">';
$c.='<option value="0">No</option><option value="1"';
if (@$vars['products_show_subcats_in_menu']=='1') {
	$c.=' selected="selected"';
}
$c.='>Yes</option></select></td></tr>';
// }
// { only show products that are in user's selected location
$c.='<tr id="products_filter_by_users_location">'
	.'<th>'.__('Filter by user\'s location').'</th><td>'
	.'<select name="page_vars[products_filter_by_users_location]">';
$c.='<option value="0">No</option><option value="1"';
if (@$vars['products_filter_by_users_location']=='1') {
	$c.=' selected="selected"';
}
$c.='>Yes</option></select></td></tr>';
// }
// { what status products to show here - active, expired, etc
$c.='<tr id="products_filter_by_users_location">'
	.'<th>'.__('What products are allowed here').'</th><td>'
	.'<select name="page_vars[products_filter_by_status]">';
$t=isset($vars['products_filter_by_status'])
	?(int)$vars['products_filter_by_status']
	:0;
$vs=array(
	__('Only show Active products'),
	__('Show Active or Inactive products'),
	__('Only show InActive products')
);
foreach ($vs as $k=>$v) {
	$c.='<option value="'.$k.'"';
	if ($k==$t) {
		$c.=' selected="selected"';
	}
	$c.='>'.$v.'</option>';
}
$c.='</select></td></tr>';
// }
$c.= '</table></div>';
// }
// { header
$c.= '<div id="products-header">';
$c.= '<p>Text to be shown above the product/product list</p>';
$c.= ckeditor('body', $page['body'], null, 1);
$c.= '</div>';
// }
// { footer
$c.= '<div id="products-footer">';
$c.= '<p>Text to be shown below the product/product list</p>';
$c.= ckeditor(
	'page_vars[footer]',
	isset($vars['footer'])?$vars['footer']:'',
	null, 1
);
$c.= '</div>';
// }
$c.= '</div>';
WW_addScript('products/admin/page-form.js');
