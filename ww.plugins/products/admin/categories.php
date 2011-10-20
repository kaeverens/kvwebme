<table>
	<tr><th style="width:300px">Categories</th><th style="width:300px">Attributes</th></tr>
	<tr><td>
<div id="categories-wrapper">
<?php
$rs=dbAll('select * from products_categories order by sortNum');
$cats=array();
foreach($rs as $r){
	if(!isset($cats[$r['parent_id']]))$cats[$r['parent_id']]=array();
	$cats[$r['parent_id']][]=$r;
}
function show_cats($id){
	global $cats;
	if(!isset($cats[$id]))return;
	echo '<ul>';
	foreach($cats[$id] as $cat) {
		echo '<li id="cat_'.$cat['id'].'"><a href="javascript:;"';
		if($cat['enabled']=='0')echo ' class="disabled"';
		echo '>'.htmlspecialchars($cat['name']).'</a>';
		show_cats($cat['id']);
		echo '</li>';
	}
	echo '</ul>';
}
show_cats(0);
echo '<script>selected_cat='.(int)$cats[0][0]['id'].';</script>';
echo '</div></td><td id="products-categories-attrs"></td></tr></table>';
WW_addScript('/j/farbtastic-1.3u/farbtastic.js');
WW_addScript('/j/jstree/jquery.jstree.js');
WW_addScript('/j/jstree/_lib/jquery.cookie.js');
WW_addScript('/j/jquery.inlinemultiselect.js');
WW_addScript('/ww.plugins/products/admin/get-product-names-js.php');
WW_addScript('/ww.plugins/products/admin/categories.js');
WW_addScript('/ww.plugins/products/admin/create-page.js');
// { uploader
WW_addScript('/ww.plugins/image-gallery/files/swfobject.js');
WW_addScript('/ww.plugins/image-gallery/files/uploadify.jquery.min.js');
WW_addCSS('/ww.plugins/image-gallery/files/uploadify.css');
