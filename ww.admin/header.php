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
	*/
function Core_adminMenuShow($items, $name=false, $prefix='', $depth=0) {
	$target=(isset($items['_target']))?' target="'.$items['_target'].'"':'';
	if (isset($items['_link'])) {
		echo '<a href="'.$items['_link'].'"'.$target.'>'.$name.'</a>';
	}
	elseif ($name!='top') {
		echo '<a href="#'.$prefix.'-'.urlencode($name).'">'.$name.'</a>';
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

$webme_start_time=microtime();
header('Content-type: text/html; Charset=utf-8');
date_default_timezone_set('Eire');
require_once dirname(__FILE__).'/../ww.incs/common.php';
// { if not logged in, show login page
if (!Core_isAdmin()) {
	require_once SCRIPTBASE . 'ww.admin/login.php';
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
WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/j/fg.menu/fg.menu.js');
WW_addScript('/j/ckeditor-3.6/ckeditor.js');
WW_addScript('/j/cluetip/jquery.cluetip.js');
WW_addScript('/ww.admin/j/admin.js');
echo '<!doctype html>
<html><head><title>WebME admin area</title>';
echo Core_getJQueryScripts();
echo '<script src="/js/'.filemtime(SCRIPTBASE.'j/js.js').'"></script>'
	.'<link rel="stylesheet" href="/j/cluetip/jquery.cluetip.css"/>'
	.'<link rel="stylesheet" href="/ww.admin/theme/admin.css"/>';
foreach ($PLUGINS as $pname=>$p) {
	if (file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/admin.css')) {
		echo '<link rel="stylesheet" href="/ww.plugins/'.$pname
			.'/admin/admin.css"/>';
	}
}
echo '</head><body';
echo '><div id="header">';
// { setup standard menu items
$menus=array(
	'Pages'=>array(
		'_link'=>'pages.php'
	),
	'Site Options'=>array(
		'General'=> array('_link'=>'siteoptions.php'),
		'Users'  => array('_link'=>'siteoptions.php?page=users'),
		'Themes' => array('_link'=>'siteoptions.php?page=themes'),
		'Plugins'=> array('_link'=>'siteoptions.php?page=plugins')
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
		$json='{"'.str_replace('>', '":{"', $name).'":{"_link":"plugin.php?_plugi'
			.'n='.$pname.'&amp;_page='.$page.'"}}'
			.str_repeat('}', substr_count($name, '>'));
		$menus=array_merge_recursive($menus, json_decode($json, true));
	}
}
// }
// { add final items
$menus['Stats']=    array('_link'=>'/ww.admin/stats.php');
$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
$menus['Help']=array( '_link'=>'http://kvweb.me/', '_target'=>'_blank');
$menus['Log Out']=  array('_link'=>'/?logout=1');
// }
// { display menu as UL list
Core_adminMenuShow($menus, 'top', 'menu');
// }
echo '</div>';
// { if maintenance mode is enabled show warning
if (@$DBVARS['maintenance-mode']=='yes') {
	echo '<div id="maintenance"><em>Maintenance Mode is currently enabled w'
		.'hich means that only administrators can view the frontend of this w'
		.'ebsite. Click <a href="siteoptions.php">here</a> to disable it.</em'
		.'></div>';
	echo '<style type="text/css">.has-left-menu{ top:130px!important; }</st'
		.'yle>';
}
// }
echo '<div id="wrapper"><div id="main">';
