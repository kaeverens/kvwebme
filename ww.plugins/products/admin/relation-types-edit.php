<?php
/**
	* form for editing product relation types
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
// { set up initial variables
if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
// }
echo '<a href="/ww.admin/plugin.php?_plugin=products&amp;_page=relation-typ'
	.'es">back to Relation Types</a>';

if (@$_REQUEST['action']='save') {
	$errors=array();
	if (!isset($_REQUEST['name']) || $_REQUEST['name']=='') {
		$errors[]='You must fill in the <strong>Name</strong>.';
	}
	if (count($errors)) {
		echo '<em>'.join('<br />', $errors).'</em>';
	}
	else {
		$sql='set name="'.addslashes($_REQUEST['name']).'",one_way='
			.(int)$_REQUEST['one_way'];
		if ($id) {
			dbQuery("update products_relation_types $sql where id=$id");
		}
		else {
			dbQuery("insert into products_relation_types $sql");
			$id=dbOne('select last_insert_id() as id', 'id');
		}
		echo '<em>Relation Type saved</em>';
		Core_cacheClear('products/relation-types');
	}
}

if ($id) {
	$tdata=dbRow("select * from products_relation_types where id=$id");
	if (!$tdata) {
		die('<em>No relation type with that ID exists.</em>');
	}
}
else {
	$tdata=array('id'=>0, 'name'=>'', 'one_way'=>0);
}
echo '<form action="'.$_url.'&amp;id='.$id.'" method="POST" enctype="multip'
	.'art/form-data"><input type="hidden" name="action" value="save" />'
	.'<table><tr><th>Name</th><td><input class="not-empty" name="name" value="'
	.htmlspecialchars($tdata['name']).'" /></td></tr><tr><th>One-Way</th><td>'
	.'<select name="one_way"><option value="0">No</option><option value="1"';
if ($tdata['one_way']) {
	echo ' selected="selected"';
}
echo '>Yes</option></select></td></tr></table>'
	.'<input type="submit" value="Save" /></form>'
	.'<script src="/ww.plugins/products/admin/types-edit.js"></script>';
