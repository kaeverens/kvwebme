<?php
echo admin_menu(array(
	'Backup'=>'/ww.admin/plugin.php?_plugin=backup&amp;_page=backup',
	'Import'=>'/ww.admin/plugin.php?_plugin=backup&amp;_page=import'
),$_url);
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/products/admin/products.css" />';
