<h1>Theme Editor</h1>
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


$type=isset($_REQUEST['type'])?$_REQUEST['type']:'';
$name=isset($_REQUEST['name'])?$_REQUEST['name']:'';

function Copy_recursive($src,$dst) {
	$dir = opendir($src);
	mkdir($dst);
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
		cache_clear('pages');
	}
}

// { menu
echo '<div class="left-menu">';
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
echo '<h2>Templates</h2>'
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
echo '<h2>CSS</h2>';
echo '<ul>';
foreach ($files as $file) {
	echo '<li><a href="/ww.admin/plugin.php?_plugin=theme-editor&amp;_page=in'
		.'dex&amp;name='.urlencode($file).'&amp;type=c">'
		.htmlspecialchars($file).'</a></li>';
}
echo '</ul>';
// }
// { other
echo '<h2>other</h2><ul>';
echo '<li><a href="/ww.admin/plugin.php?_plugin=theme-editor&amp;_page=inde'
	.'x&amp;other=restore" onclick="return confirm(\'This will overwrite your'
	.' local changes by restoring the original version of the theme.\');">res'
	.'tore</a></li>';
echo '</ul>';
// }
echo '</div>';
// }
// { content
echo '<div class="has-left-menu">';
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
WW_addScript('/ww.plugins/theme-editor/admin/menu.js');
ww_addCSS('/ww.plugins/theme-editor/admin/menu.css');
