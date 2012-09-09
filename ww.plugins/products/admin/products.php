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
	Core_quit();
}

// { links: add product, import products
echo '<a href="plugin.php?_plugin=products&amp;_page=products">'
	.__('List all products')
	.'</a> | <a href="plugin.php?_plugin=products&amp;_page=products-edit">'
	.__('Add a Product')
	.'</a> | '
	.'<a href="javascript:Core_screen(\'products\', \'js:Import\');">'
	.__('Import Products', 'core').'</a>'
	;
// }
if (!dbOne('select id from products_types limit 1', 'id')) {
	echo '<em>'
		.__('You can\'t create a product until you have created a type.')
		.' <a href="javascript:Core_screen(\'products\',\'js:Types\');">'
		.__('Click here to create a Product Type.')
		.'</a></em>';
	return;
}
$rs=dbAll('select id from products limit 1');
if (!count($rs)) {
	echo '<em>'.__('No existing products.', 'core')
		.' <a href="/ww.admin/plugin.php?_plugin=products&amp;_page=products-edit">'
		.__('Add a Product').'</a> '
		.__('or', 'core')
		.' <a href="javascript:Core_screen(\'products\', \'js:Import\');">'
		.__('Import Products', 'core').'</a>';
	return;
}

// { products list
echo '<div><table id="products-list"><thead>'
	.'<tr><th><input type="checkbox" id="products-selectall"/></th>'
	.'<th>&nbsp;</th><th>'
	.__('Name')
	.'</th><th>'
	.__('Stock Number')
	.'</th><th title="'.__('Amount In Stock').'">#</th><th>'
	.__('Owner')
	.'</th><th>'
	.__('ID')
	.'</th><th>'
	.__('Enabled')
	.'</th><th>&nbsp;</th></tr></thead><tbody>'
	.'</tbody></table></div>'
	.'<select id="products-action"><option value="0"> -- </option>'
	.'<option value="1">'.__('Delete Selected').'</option>'
	.'<option value="2">'.__('Set Disabled').'</option>'
	.'<option value="3">'.__('Set Enabled').'</option>'
	.'</select>';
// }
WW_addScript('products/admin/products.js');
