<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher/site';

$css=array();
$images=array();
$js=array();

$files=new DirectoryIterator($base);
foreach ($files as $file) {
	if ($file->isDot() || $file->isDir()) {
		continue;
	}
	$f=file_get_contents($base.'/'.$file->getFilename());
	// { get list of css files
	$css[]=array('/css/', 'ww.css.css'); // [original, new]
	preg_match_all('/"([^"]*\.css)"/', $f, $matches);
	foreach ($matches[1] as $m) {
		$css[]=array($m, str_replace('/', '@', $m));
	}
	preg_match_all("/'([^']*\.css)'/", $f, $matches);
	foreach ($matches[1] as $m) {
		$css[]=array($m, str_replace('/', '@', $m));
	}
	// }
	// { get list of image files
	preg_match_all('/"([^\("]*\.(jpg|gif|jpeg|png))"/', $f, $matches);
	foreach ($matches[1] as $m) {
		$images[]=array($m, str_replace('/', '@', $m));
	}
	preg_match_all("/\(([^'\"\)]*\.(jpg|gif|jpeg|png))\)/", $f, $matches);
	foreach ($matches[1] as $m) {
		$images[]=array($m, str_replace('/', '@', $m));
	}
	preg_match_all("/'([^\(']*\.(jpg|gif|jpeg|png))'/", $f, $matches);
	foreach ($matches[1] as $m) {
		$images[]=array($m, str_replace('/', '@', $m));
	}
	// }
	// { get list of javascript files
	$js[]=array('/j/menu.php','menu.js');
	preg_match_all('#"([^"]*\.js|/js/[0-9]*)"#', $f, $matches);
	foreach ($matches[1] as $m) {
		$js[]=array($m, str_replace('/', '@', $m));
	}
	preg_match_all("/'([^']*\.js)'/", $f, $matches);
	foreach ($matches[1] as $m) {
		$js[]=array($m, str_replace('/', '@', $m));
	}
	// }
}

file_put_contents($base.'/tmp/css.json', json_encode($css));
file_put_contents($base.'/tmp/images.json', json_encode($images));
file_put_contents($base.'/tmp/js.json', json_encode($js));
