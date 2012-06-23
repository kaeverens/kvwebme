<?php
/**
	* admin page for editing themes
	*
	* PHP Version 5.3
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL Version 2
	* @link     http://webme.kvsites.ie/
	**/

echo '<h1>'.__('Theme Editor').'</h1>';

$type=isset($_REQUEST['type'])?$_REQUEST['type']:'';
$name=isset($_REQUEST['name'])?$_REQUEST['name']:'';

function Copy_recursive($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== ($file=readdir($dir))) {
		if (($file!='.') && ($file!='..')) {
			if (is_dir($src.'/'.$file)) {
				Copy_recursive($src.'/'. $file, $dst.'/'.$file);
			}
			else {
				copy($src.'/'.$file, $dst.'/'.$file);
			}
		}
	}
	closedir($dir);
} 
if (isset($_REQUEST['other']) && $_REQUEST['other']=='restore') {
	global $DBVARS;
	if (is_dir($DBVARS['theme_dir'].'/'.$DBVARS['theme'])) {
		Copy_recursive(
			$DBVARS['theme_dir'].'/'.$DBVARS['theme'],
			$DBVARS['theme_dir_personal'].'/'.$DBVARS['theme']
		);
		Core_cacheClear('pages');
	}
}

// { menu
echo '<h2>'.__('Editor').'</h2><div class="sub-nav">';
// { html templates
$d=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h');
$files=array();
foreach ($d as $f) {
	if ($f->isDot()) {
		continue;
	}
	$fname=$f->getFileName();
	if (!preg_match('/\.html$/', $fname)) {
		continue;
	}
	$files[]=preg_replace('/\.html$/', '', $fname);
}
asort($files);
echo '<h4>'.__('Templates').'</h4>'
	.'<ul id="themeeditor-templates">';
foreach ($files as $file) {
	echo '<li><a ';
	if ($type=='h' && $name==$file) {
		echo ' class="thispage"';
	}
	echo ' href="/ww.admin/plugin.php?_plugin=theme-editor&amp;_page=index&am'
		.'p;name='.urlencode($file).'&amp;type=h">'.htmlspecialchars($file)
		.'</a><div class="actions"></div></li>';
}
echo '</ul>';
// }
// { CSS files 
$d=new DirectoryIterator(THEME_DIR.'/'.THEME.'/c');
$files=array();
foreach ($d as $f) {
	if ($f->isDot()) {
		continue;
	}
	$fname=$f->getFileName();
	if (!preg_match('/\.css$/', $fname)) {
		continue;
	}
	$files[]=preg_replace('/\.css$/', '', $fname);
}
asort($files);
echo '<h4>'.__('CSS').'</h4>';
echo '<ul id="themeeditor-css">';
foreach ($files as $file) {
	echo '<li><a href="/ww.admin/plugin.php?_plugin=theme-editor&amp;_page=in'
		.'dex&amp;name='.urlencode($file).'&amp;type=c">'
		.htmlspecialchars($file).'</a></li>';
}
echo '</ul>';
// }
echo '</div>';
// }
// { content
echo '<div class="pages_iframe">';
if (!$name || !$type || !in_array($type, array('h', 'c'))
	|| !preg_match('/^[a-z0-9_][^\/]*$/', $name)
) {
	echo '<p>Please choose a file from the left menu.</p>';
}
else{
	$name=$_REQUEST['name'];
	switch($_REQUEST['type']){
		case 'h': // {
			require 'templates.php';
		break; // }
		case 'c': // {
			require 'css.php';
		break; // }
	}
}
echo '</div>';
// }
WW_addScript('theme-editor/admin/menu.js');
ww_addCSS('/ww.plugins/theme-editor/admin/menu.css');
