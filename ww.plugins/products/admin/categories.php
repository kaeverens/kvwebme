<?php
/**
	* categories admin for products
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/** show list of categories
	*
	* @param int $id id of the parent
	*
	* @return null
	*/
function showCats($id) {
	global $cats;
	if (!isset($cats[$id])) {
		return;
	}
	echo '<ul>';
	foreach ($cats[$id] as $cat) {
		echo '<li id="cat_'.$cat['id'].'"><a href="javascript:;"';
		if ($cat['enabled']=='0') {
			echo ' class="disabled"';
		}
		echo '>'.htmlspecialchars($cat['name']).'</a>';
		showCats($cat['id']);
		echo '</li>';
	}
	echo '</ul>';
}

echo '<table style="width:100%"><tr>'
	.'<th style="width:300px">'.__('Categories').'</th>'
	.'<th>'.__('Attributes').'</th>'
	.'</tr><tr><td><div id="categories-wrapper">';
$rs=dbAll('select * from products_categories order by sortNum');
$cats=array();
foreach ($rs as $r) {
	if (!isset($cats[$r['parent_id']])) {
		$cats[$r['parent_id']]=array();
	}
	$cats[$r['parent_id']][]=$r;
}
showCats(0);
echo '<script>selected_cat='.(int)@$cats[0][0]['id'].';</script>';
echo '</div></td><td id="products-categories-attrs"></td></tr></table>';

WW_addScript('/j/farbtastic-1.3u/farbtastic.js');
// { jstree
WW_addScript('/j/jstree/jquery.jstree.js');
WW_addScript('/j/jstree/_lib/jquery.cookie.js');
// }
// { multiselect
WW_addCSS('/j/jquery.multiselect/jquery.multiselect.css');
WW_addScript('/j/jquery.multiselect/jquery.multiselect.min.js');
// }
// { uploadify
WW_addScript('image-gallery/files/uploadify.jquery.min.js');
WW_addCSS('/ww.plugins/image-gallery/files/uploadify.css');
// }
WW_addScript('products/admin/categories.js');
WW_addScript('products/admin/create-page.js');
WW_addScript('image-gallery/files/swfobject.js');
WW_addScript('/j/chosen/chosen.jquery.js');
WW_addCSS('/j/chosen/chosen.css');
WW_addCSS('/ww.plugins/products/admin/categories.css');
echo '<script src="/ww.plugins/products/admin/get-product-names-js.php">'
	.'</script>';
