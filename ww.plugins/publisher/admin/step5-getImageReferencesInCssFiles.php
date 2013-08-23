<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher/site';

$images=array();
$css=array();

$files=new DirectoryIterator($base.'/css');
foreach ($files as $file) {
	if ($file->isDot() || $file->isDir()) {
		continue;
	}
	$f=file_get_contents($base.'/css/'.$file->getFilename());
	// { get list of image files
	preg_match_all('/\([\'"]?([^\'"\)]*\.css)[\'"]?\)/', $f, $matches);
	foreach ($matches[1] as $m) {
		$css[]=array($m, str_replace('/', '@', $m));
	}
	// }
	// { get list of image files
	preg_match_all('/\([\'"]?([^\'"\)]*\.(jpg|gif|jpeg|png))[\'"]?\)/', $f, $matches);
	foreach ($matches[1] as $m) {
		$images[]=array($m, str_replace('/', '@', $m));
	}
	// }
}
file_put_contents($base.'/tmp/cssimages.json', json_encode($images));
file_put_contents($base.'/tmp/csscss.json', json_encode($css));
