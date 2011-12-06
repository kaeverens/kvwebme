<?php
// { setup the config
$plugin=array(
	'name' => 'Menu',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/menu/admin/widget-form.php',
			'js_include' => array(
				'/ww.plugins/menu/j/farbtastic/farbtastic.js',
				'/ww.plugins/menu/admin/widget-form.js'
			),
			'css_include' => '/ww.plugins/menu/j/farbtastic/farbtastic.css'
		)
	),
	'description' => 'Menu widget',
	'frontend' => array(
		'widget' => 'menu_showWidget'
	),
	'version'=>7
);
// }

require_once SCRIPTBASE.'ww.incs/menus.php';
function menu_showWidget($vars=null) {
	if ($vars && $vars->id) {
		$id=$vars->id;
		$vars=Core_cacheLoad('menus', $id, -1);
		if ($vars===-1) {
			$vars=dbRow('select * from menus where id='.$id);
			Core_cacheSave('menus', $id, $vars);
		}
	}
	return menu_show_fg($vars);
}
