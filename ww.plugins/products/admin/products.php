<?php
/**
	* Products admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/
if (!Core_isAdmin()) {
	exit;
}

// { links: add product, import products
echo '<a href="plugin.php?_plugin=products">List all products</a> | '
	.'<a href="plugin.php?_plugin=products&amp;_page=products-edit">'
	.'Add a Product</a> | '
	.'<a href="javascript:Core_screen(\'products\', \'js:Import\');"'
	.' class="__" lang-context="core">'
	.'Import</a>'
	;
// }
if (!dbOne('select id from products_types limit 1', 'id')) {
	echo '<em>You can\'t create a product until you have created a type. '
		.'<a href="javascript:Core_screen(\'products\',\'js:Types\');">Click '
		.'here to create one</a></em>';
	return;
}
$rs=dbAll('select id from products limit 1');
if (!count($rs)) {
	echo '<em>No existing products. <a href="plugin.php?_plugin=products&amp;'
		.'_page=products-edit">Click here to create one</a>.'
		.' or <a href="javascript:Core_screen(\'products\', \'js:Import\');"'
	  .' class="__" lang-context="core">import</a> a list of them';
	return;
}
// { products list
echo '<div><table id="products-list"><thead>'
	.'<tr><th>&nbsp;</th><th>Name</th>'
	.'<th>Stock Number</th><th title="in stock">#</th><th>Owner</th>'
	.'<th>ID</th><th>Enabled</th><th>&nbsp;</th></tr></thead><tbody>'
	.'</tbody></table></div>';
// }
WW_addScript('/j/jquery.jeditable.mini.js');
WW_addScript('products/admin/products.js');
