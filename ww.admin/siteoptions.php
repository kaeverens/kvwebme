<?php
require 'header.php';
echo '<h1>Site Options</h1>';

function Core_verifyAdminPage($validlist, $default, $val) {
	foreach ($validlist as $v) {
		if ($v==$val) {
			return $val;
		}
	}
	return $default;
}
echo admin_menu(
	array(
		'General'=>'siteoptions.php?page=general',
		'Users'=>'siteoptions.php?page=users',
		'Themes'=>'siteoptions.php?page=themes',
		'Plugins'=>'siteoptions.php?page=plugins'
	)
);

$page=Core_verifyAdminPage(
	array('general', 'users', 'themes', 'plugins'),
	'general',
	isset($_REQUEST['page'])?$_REQUEST['page']:''
);

echo '<div class="has-left-menu">';
require 'siteoptions/'.$page.'.php';
echo '</div>';
require 'footer.php';
