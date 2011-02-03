<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$selected=isset($_REQUEST['selected'])?$_REQUEST['selected']:0;
$products=dbAll('select id,name from products order by name');
echo '<option value=""> -- please choose -- </option>';
foreach ($products as $product) {
	echo '<option value="'.$product['id'].'"';
	if ($product['id']==$selected) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars($product['name']).'</option>';
}
