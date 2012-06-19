<?php
/**
	* Backup plugin admin menu
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo Core_adminSideMenu(
	array(
		'Backup'=>'/ww.admin/plugin.php?_plugin=backup&amp;_page=backup',
		__('Import')=>'/ww.admin/plugin.php?_plugin=backup&amp;_page=import'
	),
	$_url
);
echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/products/adm'
	.'in/products.css" />';
