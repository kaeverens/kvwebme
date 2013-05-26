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
	'version'=>9
);
// }

require_once SCRIPTBASE.'ww.incs/menus.php';
function menu_showWidget($vars=null) {
	WW_addScript('/j/fg.menu/fg.menu.js');
	WW_addCSS('/j/fg.menu/fg.menu.css');
	return '<a style="display:none"'
		.' href="'.$GLOBALS['PAGEDATA']->getRelativeUrl.'?cmsspecial=sitemap">'
		.'sitemap</a>'
		.'<script src="/a/p=menu/f=getHtml/vars='.urlencode(json_encode($vars)).'">'
		.'</script>';
}
