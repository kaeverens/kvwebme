<?php
/**
	* aggregate javascript files
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../ww.incs/basics.php';

header('Cache-Control: max-age=2592000, public');
header('Expires-Active: On');
header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
header('Pragma:');
header('Content-type: text/javascript;');

$files=array(
	'jquery.json-2.2.min.js',
	'js.js',
	'lang.js'
);
if (isset($_REQUEST['extra'])) {
	$fs=explode('*', $_REQUEST['extra']);
	array_shift($fs);
	foreach ($fs as $f) {
		if (strpos($f, '..')!==false) {
			continue;
		}
		if (strpos($f, '/')===false) {
			$f='/ww.plugins/'.$f.'/js.js';
		}
		elseif ($f{0}!='/') { // {
			$f='/ww.plugins/'.$f;
		}
		$fname=SCRIPTBASE.$f;
		if (!preg_match('/\.js$/', $fname) || !file_exists($fname)) {
			continue;
		}
		$files[]=$fname;
	}
}

$latest=0;
foreach ($files as $f) {
	$mt=filemtime($f);
	if ($mt>$latest) {
		$latest=$mt;
	}
}
$mt=filemtime(__FILE__);
if ($mt>$latest) {
	$latest=$mt;
}

$name=md5(join('|', $files));

if (file_exists(USERBASE.'/ww.cache/j/js-'.$name)
	&& filemtime(USERBASE.'/ww.cache/j/js-'.$name)<$latest
) {
	unlink(USERBASE.'/ww.cache/j/js-'.$name);
}

if (!file_exists(USERBASE.'/ww.cache/j/js-'.$name)) {
	$js='';
	foreach ($files as $f) {
		if (file_exists($f.'.m')) {
			$js.=file_get_contents($f.'.m').';';
		}
		else {
			$js.=file_get_contents($f).';';
		}
	}
	if (!file_exists(USERBASE.'/ww.cache/j')) {
		mkdir(USERBASE.'/ww.cache/j');
	}
	file_put_contents(USERBASE.'/ww.cache/j/js-'.$name, $js);
}
// { send timing header
global $starttimeCount, $starttime;
header(
	'X-RenderTime-'.($starttimeCount++).'-totalSetup: '.((microtime(true)-$starttime)*1000)
);
$starttime=microtime(true);
// }
readfile(USERBASE.'/ww.cache/j/js-'.$name);
