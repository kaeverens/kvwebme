<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher/site';
$page_names=json_decode(file_get_contents($base.'/tmp/page_names.json'));

$files=new DirectoryIterator($base);
foreach ($files as $file) {
	if ($file->isDot() || $file->isDir()) {
		continue;
	}
	$f=file_get_contents($base.'/'.$file->getFilename());
	foreach ($page_names as $p) {
		$f=str_replace('"'.$p[0].'"', '"'.$p[1].'"', $f);
		$f=str_replace("'".$p[0]."'", "'".$p[1]."'", $f);
	}
	echo $file->getFilename()."\n";
	file_put_contents($base.'/'.$file->getFilename(), $f);
}
