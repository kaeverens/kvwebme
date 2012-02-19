<?php
/**
	* show the header of the admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* function for showing the admin menu
	*
	* @param array  $items  array of menu items
	* @param string $name   the name of the menu parent
	* @param string $prefix id prefix for elements
	* @param int    $depth  depth in the tree that these links are
	*
	* @return null
	*/
function Core_adminMenuShow($items, $name=false, $prefix='', $depth=0) {
	$target=(isset($items['_target']))?' target="'.$items['_target'].'"':'';
	if (isset($items['_link'])) {
		if (strpos($items['_link'], 'javascript:')===0) {
			$link='href="#" onclick="'.str_replace(
				'javascript:',
				'',
				$items['_link']
			)
			.';return false"';
		}
		else {
			$link='href="'.$items['_link'].'"';
		}
		echo '<a '.$link.$target.'>'.__($name, 'menu').'</a>';
	}
	elseif ($name!='top') {
		echo '<a href="#'.$prefix.'-'.urlencode($name).'">'.__($name, 'menu')
			.'</a>';
	}
	if (count($items)==1 && isset($items['_link'])) {
		return;
	}
	$submenus=0;
	foreach ($items as $subitems) {
		if (is_array($subitems)) {
			$submenus++;
		}
	}
	if (!$submenus) {
		return;
	}
	if ($depth<2) {
		echo '<div id="'.$prefix.'-'.urlencode($name).'">';
	}
	echo '<ul>';
	foreach ($items as $iname=>$subitems) {
		if (!is_array($subitems)) {
			continue;
		}
		echo '<li>';
		Core_adminMenuShow($subitems, $iname, $prefix.'-'.$name, $depth+1);
		echo '</li>';
	}
	echo '</ul>';
	if ($depth<2) {
		echo '</div>';
	}
}

header('Content-type: text/html; Charset=utf-8');
define('IN_ADMIN', 1);
date_default_timezone_set('Eire');
require_once dirname(__FILE__).'/../ww.incs/common.php';
// { if not logged in, show login page
if (!Core_isAdmin()) {
	require_once SCRIPTBASE.'ww.incs/login-admin.php';
	exit;
}
// }
require SCRIPTBASE . 'ww.admin/admin_libs.php';
$admin_vars=array();
// { common variables
foreach (array('action','resize') as $v) {
	$$v=@$_REQUEST[$v];
}
foreach (array('show_items','start') as $v) {
	$$v=(int)@$_REQUEST[$v];
}
$id=isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;
// }
// { scripts
WW_addScript('/ww.admin/j/admin.js');
WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/j/fg.menu/fg.menu.js');
WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
WW_addScript('/j/cluetip/jquery.cluetip.js');
WW_addScript('/j/jquery.uploadify/jquery.uploadify.js');
WW_addScript('/j/jquery-ui-timepicker-addon.js');
// }
// { css
WW_addCSS('/j/cluetip/jquery.cluetip.css');
WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
WW_addCSS('/ww.admin/theme/admin.css');
// }
echo '<!doctype html>
<html><head><title>'.__('WebME admin area').'</title>';
foreach ($PLUGINS as $pname=>$p) {
	if (file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/admin.css')) {
		WW_addCSS('/ww.plugins/'.$pname.'/admin/admin.css');
	}
}
echo WW_getCSS();
echo Core_getJQueryScripts()
	.'<script src="/js/'.filemtime(SCRIPTBASE.'j/js.js').'"></script>';
WW_addInlineScript('var sessid="'.session_id().'";');
// { languages
$langs=dbAll(
	'select code,name from language_names order by is_default desc,code,name'
);
echo '<script>var languages='.json_encode($langs).';</script>';
// }
echo '</head><body';
echo '><div id="header">';
// { setup standard menu items
$menus=array(
	'Pages'=>array(
		'_link'=>'pages.php'
	),
	'Site Options'=>array(
		'General'=> array('_link'=>'siteoptions.php'),
		'Languages'=>array(
			'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Languages\')'
		),
		'Locations'=>array(
			'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Locations\')'
		),
		'Users'  => array('_link'=>'siteoptions.php?page=users'),
		'Plugins'=> array('_link'=>'siteoptions.php?page=plugins'),
#		'Stats2'=>array(
#			'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Stats\')'
#		),
		'Themes' => array('_link'=>'siteoptions.php?page=themes'),
		'Timed Events'=>array(
			'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Cron\')'
		)
	)
);
// }
// { add custom items (from plugins)
foreach ($PLUGINS as $pname=>$p) {
	if (!isset($p['admin']) || !isset($p['admin']['menu'])) {
		continue;
	}
	foreach ($p['admin']['menu'] as $name=>$page) {
		if (preg_match('/[^a-zA-Z0-9 >]/', $name)) {
			continue; // illegal characters in name
		}
		$link=strpos($page, 'js:')===false
			?'plugin.php?_plugin='.$pname.'&amp;_page='.$page
			:'javascript:Core_screen(\''.$pname.'\', \''.$page.'\');';
		$json='{"'.str_replace('>', '":{"', $name).'":{"_link":"'.$link.'"}}'
			.str_repeat('}', substr_count($name, '>'));
		$menus=array_merge_recursive($menus, json_decode($json, true));
	}
}
// }
// { add final items
$menus['Site Options']['Stats']=array('_link'=>'/ww.admin/stats.php');
$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
$menus['Help']=array( '_link'=>'http://kvweb.me/', '_target'=>'_blank');
$menus['Log Out']=  array('_link'=>'/?logout=1');
$menus['Misc']['File Manager']=array(
	'_link'=>'javascript:return window.open(\'/j/kfm/\', \'kfm\', '
	.'\'modal,width=800,height=640\')'
);
// }
// { display menu as UL list
Core_adminMenuShow($menus, 'top', 'menu');
// }
echo '</div>';
// { if maintenance mode is enabled show warning
if (@$DBVARS['maintenance-mode']=='yes') {
	echo '<div id="maintenance"><em>'.__(
		'Maintenance Mode is currently enabled which means that only administra'
		.'tors can view the frontend of this website. Click <a href="siteoption'
		.'s.php">here</a> to disable it.'
	)
	.'</em></div><style type="text/css">.has-left-menu{ top:130px!important;}'
	.'</style>';
}
// }
echo '<div id="wrapper"><div id="main">';
