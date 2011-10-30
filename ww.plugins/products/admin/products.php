<?php
if (!Core_isAdmin()) {
	exit;
}

// { links: add product, import products
echo '<a href="plugin.php?_plugin=products">List all products</a> | '
	.'<a href="plugin.php?_plugin=products&amp;_page=products-edit">'
	.'Add a Product</a> | '
	.'Import Products: '
	.'<a href="plugin.php?_plugin=products&amp;_page=import">CSV</a> / '
	.'<a href="plugin.php?_plugin=products&amp;_page=import-json">JSON</a>'
	;
// }

if (isset($_REQUEST['delete']) && is_numeric($_REQUEST['delete'])) {
	if (isset($_REQUEST['delete-images'])&&($_REQUEST['delete-images']==1)) {
		$imagesDir
			= dbOne(
				'select images_directory
				from products
				where id='.$_REQUEST['delete'],
				'images_directory'
			);
		$id = kfm_api_getDirectoryId($imagesDir);
		if ($id) {
			$dir = kfmDirectory::getInstance($id);
			if ($dir) {
				$dir->delete();
			}
		}
	}
	dbQuery('delete from products where id='.$_REQUEST['delete']);
	echo '<em>Product deleted.</em>';
}

if (!dbOne('select id from products_types limit 1','id')) {
	echo '<em>You can\'t create a product until you have created a type. '
		.'<a href="javascript:Core_screen(\'products\',\'js:Types\');">Click '
		.'here to create one</a></em>';
	return;
}
$rs=dbAll('select id,images_directory,name,stock_number,enabled from products order by name');
if(!count($rs)){
	echo '<em>No existing products. <a href="plugin.php?_plugin=products&amp;'
		.'_page=products-edit">Click here to create one</a>.'
		.' or import from '
		.'<a href="plugin.php?_plugin=products&amp;_page=import">CSV</a> or '
		.'<a href="plugin.php?_plugin=products&amp;_page=import-json">JSON</a>';
	return;
}

// { products list
echo '<div><table class="datatable"><thead><tr><th>&nbsp;</th><th>Name</th><th>Stock Number'
	.'</th><th>ID</th><th>Enabled</th><th>&nbsp;</th></tr></thead><tbody>';
foreach($rs as $r){
	$link='plugin.php?_plugin=products&amp;_page=products-edit&amp;id='.$r['id'];
	// { has images
	$has_images=0;
	$dir_id=kfm_api_getDirectoryId(
		preg_replace('/^\//', '', $r['images_directory'])
	);
	if ($dir_id) {
		$images=kfm_loadFiles($dir_id);
		$images=$images['files'];
		$has_images=count($images);
	}
	$img=$has_images
		?'<!-- '.$has_images.' --><div title="has images" '
		.'class="ui-icon ui-icon-image"></div>'
		:'';
	// }
	echo '<tr id="product-row-'.$r['id'].'">'
		.'<td>'.$img.'</td>'
		.'<td class="edit-link"><!-- '.htmlspecialchars($r['name']).' -->'
		.'<a href="'.$link.'">'.htmlspecialchars($r['name']).'</td>'
		.'<td class="edit-link"><!-- '.htmlspecialchars($r['stock_number']).' -->'
		.'<a href="'.$link.'">'.htmlspecialchars($r['stock_number']).'</td>'
		.'<td>'.$r['id'].'</td>'
		.'<td>'.($r['enabled']=='1'?'Yes':'No').'</td>'
		.'<td><a class="delete-product" href="javascript:;" title="delete">[x]</a>'
		.'</td></tr>';
}
echo '</tbody></table></div>';
// }

WW_addScript('/ww.plugins/products/admin/products.js');
