<?php
/**
	* load up a plugin's admin page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'header.php';
$pname=isset($_REQUEST['_plugin'])?$_REQUEST['_plugin']:'';
$pagename=isset($_REQUEST['_page'])?$_REQUEST['_page']:'';
if (preg_match('/[^\-a-zA-Z0-9]/', $pagename) || $pagename=='') {
	die(__('illegal character in page name'));
}
if (!isset($PLUGINS[$pname])) {
	die('no plugin of that name ('.htmlspecialchars($pname).') exists');
}
$plugin=$PLUGINS[$pname];
$_url='/ww.admin/plugin.php?_plugin='.urlencode($pname).'&amp;_page='.$pagename;
WW_addScript('/ww.admin/j/plugins.js');
// { help pages
$help=array();
if (file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/docs/admin.html')) {
	$help[]=array('admin', 'documentation');
}
if (file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/docs/design.html')) {
	$help[]=array('design', 'design docs');
}
if (count($help)) {
	echo '<div id="nav-help" style="width:150px;">';
	foreach ($help as $h) {
		echo '<a href="javascript:show_help(\''.$pname.'\',\''.$h[0].'\')">'
			.__($h[1], 'plugins').'</a>';
	}
	echo '</div>';
}
// }
// { display the plugin
echo '<h1>'.htmlspecialchars(__($pname, 'plugins')).'</h1>';
if (!file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/'.$pagename.'.php')) {
	echo '<em>The <strong>'.htmlspecialchars($pname)
		.'</strong> plugin does not have an admin page named <strong>'
		.$pagename.'</strong>.</em>';
}
else {
	if (file_exists(SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/menu.php')) {
		require_once SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/menu.php';
		echo '<div class="has-left-menu">';
		require_once SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/'.$pagename.'.php';
		echo '</div>';
	}
	else {
		require_once SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/'.$pagename.'.php';
	}
}
// }
require_once 'footer.php';
