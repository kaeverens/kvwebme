<?php
require_once '../../ww.incs/basics.php';
require_once '../admin_libs.php';
if (!Core_isAdmin()) {
	exit;
}
$selected=isset($_REQUEST['selected'])?$_REQUEST['selected']:0;
foreach ($pagetypes as $a) {
	echo '<option value="'.$a[0].'">'.htmlspecialchars($a[1]).'</option>';
}
$plugin=false;
foreach ($PLUGINS as $n=>$p) {
	if (isset($p['admin']['page_type'])) {
		if (is_array($p[ 'admin' ][ 'page_type' ])) {
			foreach ($p[ 'admin' ][ 'page_type' ] as $name => $type) {
				echo '<option value="' . htmlspecialchars($name) . '">'
					. htmlspecialchars($name) . '</option>';
			}
		}
		else {
			if (!is_int($selected) && $selected==$n) {
				$plugin=$p;
			}
			echo '<option value="'.htmlspecialchars($n).'">'
				.htmlspecialchars($n).'</option>';
		}
	}
}
