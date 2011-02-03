<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}

// { parent category
echo '<strong>Parent Category</strong><br />'
	.'<select name="parent_cat" id="parent_cat_'.$id.'">';
if (!isset($_REQUEST['parent_cat'])) {
	echo '<option value="0"> -- please choose -- </option>';
}
else {
	echo '<option value="'.$_REQUEST['parent_cat'].'">'
		.dbOne(
			'select name from products_categories where id='
			.$_REQUEST['parent_cat'],
			'name'
		)
		.'</option>';
}
echo '</select>';
// }
// { diameter
echo '<strong>Diameter</strong>';
$diameter=(isset($_REQUEST['diameter']) && $_REQUEST['diameter'])
	?((int)$_REQUEST['diameter'])
	:280;
echo '<input name="diameter" value="'.htmlspecialchars($diameter).'" />';
echo '<script>$("#parent_cat_'.$id.'").remoteselectoptions({url:'
	.'"/ww.plugins/products/admin/get-all-categories.php"});</script>';
