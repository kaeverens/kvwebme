<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

$selected=$_REQUEST['selected'];

function ProductCategories_getAll($selected, $parent=0, $depth=0) {
	$rs=dbAll(
		'select id,name from products_categories where parent_id='.$parent
		.' order by name'
	);
	foreach ($rs as $r) {
		echo '<option value="'.$r['id'].'"';
		if ($r['id']==$selected) {
			echo ' selected="selected"';
		}
		echo '>'.str_repeat('&raquo; ', $depth)
		.htmlspecialchars($r['name']).'</option>';
		ProductCategories_getAll($selected, $r['id'], $depth+1);
	}
}
echo '<option value="0"> -- top -- </option>';
ProductCategories_getAll($selected);
