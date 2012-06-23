<?php
/**
	* find any template at all and use it
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$d=array();
if (!file_exists(THEME_DIR.'/'.THEME.'/h/')) {
	die(__('no theme installed. please <a href="/ww.admin/">install one</a>'));
}
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
$template=$d[0];
if ($template=='') {
	die(__('no template created. please create a template first'));
}
