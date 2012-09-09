<?php
/**
	* product relation types
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
echo '<p>Relation Types describe how products are related to each other. '
	.'For example, "Also Bought", "Similar", "Part Of".</p>';
if (isset($_REQUEST['delete']) && is_numeric($_REQUEST['delete'])) {
	dbQuery('delete from products_relation_types where id='.$_REQUEST['delete']);
	echo '<em>Relation Type deleted.</em>';
}
$rs=dbAll('select id,name from products_relation_types order by name');
if (!count($rs)) {
	echo '<em>No existing relation types. <a href="plugin.php?_plugin=product'
		.'s&amp;_page=relation-types-edit">Click here to create one</a>.</em>';
}
else {
	echo '<a href="plugin.php?_plugin=products&amp;_page=relation-types-edit">'
		.'Add a new relation type</a>'
		.'<div style="width:50%"><table class="datatable">'
		.'<thead><tr><th>Name</th><th>&nbsp;</th></tr></thead><tbody>';
	foreach ($rs as $r) {
		echo '<tr><td class="edit-link"><a href="plugin.php?_plugin=products&amp;'
			.'_page=relation-types-edit&amp;id='.$r['id'].'">'
			.htmlspecialchars($r['name']).'</td><td>'
			.'<a href="'.$_url.'&delete='.$r['id'].'" onclick="'
			.'return confirm(\'are you sure you want to delete the '
			.htmlspecialchars($r['name']).' product type?\')" title="delete">[x]'
			.'</a></td></tr>';
	}
	echo '</tbody></table></div>';
}
