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

header('Content-type: text/html; Charset=utf-8');
define('IN_ADMIN', 1);
date_default_timezone_set('Eire');
require_once dirname(__FILE__).'/../ww.incs/common.php';
// { if not logged in, show login page
if (!Core_isAdmin()) {
	require_once SCRIPTBASE.'ww.incs/login-admin.php';
	Core_quit();
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
WW_addScript('/j/jquery.saorfm/jquery.saorfm.js');
WW_addScript('/j/jquery-ui-timepicker-addon.js');
WW_addScript('/j/jquery.uploadify/jquery.uploadify.js');
WW_addScript('/j/uploader.js');
WW_addScript('/j/lang.js');
WW_addScript('/j/CodeMirror-2.24/lib/codemirror.js');
WW_addScript('/j/CodeMirror-2.24/mode/css/css.js');
WW_addScript('/j/CodeMirror-2.24/mode/javascript/javascript.js');
WW_addScript('/j/CodeMirror-2.24/mode/xml/xml.js');
WW_addScript('/j/CodeMirror-2.24/mode/smarty/smarty.js');
WW_addScript('/j/CodeMirror-2.24/mode/htmlmixed/htmlmixed.js');
// }
// { css
WW_addCSS('/j/cluetip/jquery.cluetip.css');
WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
WW_addCSS('/j/jquery.saorfm/jquery.saorfm.css');
WW_addCSS('/ww.admin/theme/admin.css');
WW_addCSS('/j/CodeMirror-2.24/lib/codemirror.css');
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
echo Core_languagesGetUi(array('type'=>'selectbox'));
// { if maintenance mode is enabled show warning
if (@$DBVARS['maintenance-mode']=='yes') {
	echo '<div id="maintenance"><em>'.__(
		'Maintenance Mode is currently enabled which means that only'
		.' administrators can view the frontend of this website.'
		.' Click <a href="siteoptions.php">here</a> to disable it.'
	)
	.'</em></div><style type="text/css">.pages_iframe{ top:130px!important;}'
	.'</style>';
}
// }
echo '<div id="wrapper"><div id="main">';
