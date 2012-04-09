<?php
echo Core_adminSideMenu(array(
	'Products'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=products',
	'Categories'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=categories',
	'Types'=>'javascript:Core_screen(\'products\',\'Types\')',
	'Relation Types'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=relation-types',
	'Import'=>'javascript:Core_screen(\'products\',\'Import\')',
	'Export Data'=>'javascript:Core_screen(\'products\',\'ExportData\')',
	'Brands and Producers' =>'javascript:Core_screen(\'products\',\'BrandsandProducers\')'
),$_url);
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/products/admin/products.css" />';
