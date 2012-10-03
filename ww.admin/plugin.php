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
		echo '<div class="pages_iframe">';
		require_once SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/'.$pagename.'.php';
		echo '</div>';
	}
	else {
		require_once SCRIPTBASE.'/ww.plugins/'.$pname.'/admin/'.$pagename.'.php';
	}
}
// }
require_once 'footer.php';
