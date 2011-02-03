<?php
echo admin_menu(array(
	'Products'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=products',
	'Categories'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=categories',
	'Types'=>   '/ww.admin/plugin.php?_plugin=products&amp;_page=types',
	'Relation Types'=>'/ww.admin/plugin.php?_plugin=products&amp;_page=relation-types'
),$_url);
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/products/admin/products.css" />';
