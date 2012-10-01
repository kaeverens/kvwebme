<?php
// { setup the config
$plugin=array(
	'name' => function() {
		return __('Menu');
	},
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
	'description' =>function() {
		return __('Menu widget');
	},
	'frontend' => array(
		'widget' => 'menu_showWidget'
	),
	'version'=>7
);
// }

require_once SCRIPTBASE.'ww.incs/menus.php';
function menu_showWidget($vars=null) {
	if ($vars && isset($vars->id) && $vars->id) {
		$id=$vars->id;
		$vars=Core_cacheLoad('menus', $id, -1);
		if ($vars===-1) {
			$vars=dbRow('select * from menus where id='.$id);
			Core_cacheSave('menus', $id, $vars);
		}
		if ($vars['parent']=='-1') {
			global $PAGEDATA;
			$pid=$PAGEDATA->id;
			if ($pid) {
				$n=dbOne('select id from pages where parent='.$pid.' limit 1', id);
				if (!$n) {
					$pid=(int)$PAGEDATA->parent;
					if (!$pid) {
						return '';
					}
				}
			}
			$vars['parent']=$pid;
		}
	}
	return Core_menuShowFg($vars);
}
