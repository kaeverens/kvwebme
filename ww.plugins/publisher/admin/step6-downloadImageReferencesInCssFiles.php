<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher/site';
$images=json_decode(file_get_contents($base.'/tmp/cssimages.json'));
$css=json_decode(file_get_contents($base.'/tmp/csscss.json'));

$files=new DirectoryIterator($base.'/css');
foreach ($files as $file) {
	if ($file->isDot() || $file->isDir()) {
		continue;
	}
	$f=file_get_contents($base.'/css/'.$file->getFilename());
	foreach ($images as $p) {
continue;
		if (!file_exists($base.'/images/'.$p[1])) {
			$f2=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/'.$p[0]);
			file_put_contents($base.'/images/'.$p[1], $f2);
		}
		$f=str_replace('"'.$p[0].'"', '"../images/'.$p[1].'"', $f);
		$f=str_replace('('.$p[0].')', '(../images/'.$p[1].')', $f);
		$f=str_replace("'".$p[0]."'", "'../images/".$p[1]."'", $f);
	}
	foreach ($css as $p) {
		if (!file_exists($base.'/css/'.$p[1])) {
			$f2=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/'.$p[0]);
			file_put_contents($base.'/css/'.$p[1], $f2);
		}
		$f=str_replace('"'.$p[0].'"', '"'.$p[1].'"', $f);
		$f=str_replace('('.$p[0].')', '('.$p[1].')', $f);
		$f=str_replace("'".$p[0]."'", "'".$p[1]."'", $f);
	}
	echo $file->getFilename()."\n";
	file_put_contents($base.'/css/'.$file->getFilename(), $f);
}
