<?php
/**
	* widget admin form for send-as-email plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}

// { template to use
if (isset($_REQUEST['template'])) {
	$template=$_REQUEST['template'];
}
else {
	$template='';
}
echo '<strong>Template to use</strong><br />';
echo '<select name="template"><option value=""> -- choose -- </option>';
$dir=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h/');
foreach ($dir as $f) {
	if ($f->isDot()) {
		continue;
	}
	$n=$f->getFilename();
	if (preg_match('/\.html$/', $n)) {
		$d[]=preg_replace('/\.html$/', '', $n);
	}
}
asort($d);
if (count($d)>1) {
	foreach ($d as $name) {
		echo '<option ';
		if ($name==$template) {
			echo ' selected="selected"';
		}
		echo '>'.$name.'</option>';
	}
}
echo '</select>';
