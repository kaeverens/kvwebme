<?php
echo admin_menu(array(
	'Products'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=products',
	'Categories'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=categories',
	'Types'=>   'javascript:Core_screen(\'products\',\'Types\')',
	'Relation Types'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=relation-types',
	'Export Data'=>   'javascript:Core_screen(\'products\',\'ExportData\')'
),$_url);
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/products/admin/products.css" />';
