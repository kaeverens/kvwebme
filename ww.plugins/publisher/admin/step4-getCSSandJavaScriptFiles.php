<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$base=USERBASE.'/ww.cache/publisher/site';
$css=json_decode(file_get_contents($base.'/tmp/css.json'));
$images=json_decode(file_get_contents($base.'/tmp/images.json'));
$js=json_decode(file_get_contents($base.'/tmp/js.json'));

@mkdir($base.'/css');
@mkdir($base.'/images');
@mkdir($base.'/js');

$files=new DirectoryIterator($base);
foreach ($files as $file) {
	if ($file->isDot() || $file->isDir()) {
		continue;
	}
	$f=file_get_contents($base.'/'.$file->getFilename());
	foreach ($css as $p) {
		if (!file_exists($base.'/css/'.$p[1])) {
			$f2=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/'.$p[0]);
			file_put_contents($base.'/css/'.$p[1], $f2);
		}
		$f=str_replace('"'.$p[0].'"', '"css/'.$p[1].'"', $f);
		$f=str_replace("'".$p[0]."'", "'css/".$p[1]."'", $f);
	}
	foreach ($images as $p) {
		if (!file_exists($base.'/images/'.$p[1])) {
			if (preg_match('#^/f/#', $p[0])) { // direct copy will work
				copy(USERBASE.'/'.$p[0], $base.'/images/'.$p[1]);
			}
			else { // download (may be parsed or external
				$f2=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/'.$p[0]);
				file_put_contents($base.'/images/'.$p[1], $f2);
			}
			echo USERBASE.'/'.$p[0]."\n";
		}
		$f=str_replace('"'.$p[0].'"', '"images/'.$p[1].'"', $f);
		$f=str_replace('('.$p[0].')', '(images/'.$p[1].')', $f);
		$f=str_replace("'".$p[0]."'", "'images/".$p[1]."'", $f);
	}
	foreach ($js as $p) {
		if (!file_exists($base.'/js/'.$p[1])) {
			$f2=file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/'.$p[0]);
			$f2=str_replace("'/j/menu.php?pageid='+pagedata.id", "'js/menu.js'", $f2);
			file_put_contents($base.'/js/'.$p[1], $f2);
		}
		$f=str_replace('"'.$p[0].'"', '"js/'.$p[1].'"', $f);
		$f=str_replace("'".$p[0]."'", "'js/".$p[1]."'", $f);
	}
	file_put_contents($base.'/'.$file->getFilename(), $f);
}
