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
WW_addScript('/j/fg.menu/fg.menu.js');
// { languages
$langs=dbAll(
	'select code,name from language_names order by is_default desc,code,name'
);
echo '<script>var languages='.json_encode($langs).';</script>';
// }
echo '</head><body';
echo '><div id="header"></div>';
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
