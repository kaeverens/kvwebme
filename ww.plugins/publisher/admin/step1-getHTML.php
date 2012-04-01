<?php
/**
	* retrieve HTML for the website
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

// { rrmdir

/**
	* recursively remove a directory
	*
	* @param string $dir the directory
	*
	* @return null
	*/
function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") {
					rrmdir($dir."/".$object);
				}
				else {
					unlink($dir."/".$object);
				}
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

// }

$base=USERBASE.'/ww.cache/publisher';
if (file_exists($base)) {
	rrmdir($base);
}
mkdir($base);
file_put_contents(
	$base.'/website.html',
	'<script>document.location="site/index.html";</script>'
);
$base=$base.'/site';
mkdir($base);

$pids=dbAll('select id from pages');
$page_names=array();
foreach ($pids as $pid) {
	$page=Page::getInstance($pid['id']);
	$url='http://'.$_SERVER['HTTP_HOST'].$page->getRelativeURL();
	$relname=$page->getRelativeURL();
	$name=preg_replace('#^/#', '', $relname);
	$name=str_replace('/', '@', $name).'.html';
	$f=file_get_contents($url);
	file_put_contents($base.'/'.$name, $f);
	if ($page->special&1) {	// home page
		file_put_contents($base.'/index.html', $f);
	}
	$page_names[]=array($relname, $name);
}
mkdir($base.'/tmp');
file_put_contents($base.'/tmp/page_names.json', json_encode($page_names));
