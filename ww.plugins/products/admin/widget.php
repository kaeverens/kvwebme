<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
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
// { type
echo '<br /><strong>Type</strong>';
echo '<select name="widget_type"><option>Pie-Chart</option>';
echo '<option';
if (isset($_REQUEST['widget_type']) && $_REQUEST['widget_type']=='List Categories') {
	echo ' selected="selected"';
}
echo '>List Categories</option>';
echo '</select>';
// }
// { diameter
echo '<div class="diameter"><strong>Diameter</strong>';
$diameter=(isset($_REQUEST['diameter']) && $_REQUEST['diameter'])
	?((int)$_REQUEST['diameter'])
	:280;
echo '<input name="diameter" value="'.htmlspecialchars($diameter).'" /></div>';
// }
echo '<script>$("#parent_cat_'.$id.'").remoteselectoptions({url:'
	.'"/a/p=products/f=categoriesOptionsGet"});'
	.'Products_widgetTypeChanged();</script>';
