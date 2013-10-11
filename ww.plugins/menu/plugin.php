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
	global $DBVARS;
	$cdn=isset($DBVARS['cdn'])?'//'.$DBVARS['cdn']:'';
	$pageurl=preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
	return '<a style="display:none"'
		.' href="'.$pageurl.'?cmsspecial=sitemap">'
		.'sitemap</a>'
		.'<script src="'.$cdn.'/a/p=menu/f=getHtml/vars='
		.urlencode(json_encode($vars)).'">'
		.'</script>';
}
