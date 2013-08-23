<?php
require_once '../ww.incs/basics.php';
if (!Core_isAdmin()) {
	Core_quit();
}
require_once '../ww.incs/api-funcs.php';

$exclude=array(
	'#^/j/kfm#',
	'#^/ww.css/Minify#',
	'#^/j/ckeditor#',
	'#^/ww.incs/dompdf#',
	'#^/ww.incs/Smarty#',
	'#^/j/lang.js#',
	'#^/j/CodeMirror#'
);
$found=array();
$wrong_format=array();

function findTranslations($basedir) {
	global $found, $exclude, $wrong_format;
	$files=new DirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/'.$basedir);
	foreach ($files as $file) {
		$fname=$file->getFilename();
		$excludeThis=false;
		foreach ($exclude as $regexp) {
			if (preg_match($regexp, $basedir.'/'.$fname)) {
				$excludeThis=true;
			}
		}
		if ($excludeThis) {
			continue;
		}
		if ($file->isDot()) {
			continue;
		}
		if ($file->isDir()) {
			findTranslations($basedir.'/'.$fname);
			continue;
		}
		if (!preg_match('/\.(php|js)$/', $fname)) {
			continue;
		}
		$file=file_get_contents(
			$_SERVER['DOCUMENT_ROOT'].$basedir.'/'.$fname
		);
		if (strpos($file, '"__"')!==false) {
			$wrong_format[]=$_SERVER['DOCUMENT_ROOT'].$basedir.'/'.$fname;
			continue;
		}
		if (strpos($file, '__(')===false) {
			continue;
		}
		echo $_SERVER['DOCUMENT_ROOT'].$basedir.'/'.$fname.'<br/>';
		preg_match_all('/__\(\s*\'(.*?)\'\s*\)/sm', $file, $matches);
		echo '<ul>';
		foreach ($matches[1] as $m) {
			$m=preg_replace('/\',\s*array\(.*?\),\s*\'/sm', "', '", $m);
			$m=preg_replace('/\',\s*\'/sm', "', '", $m);
			$bits=explode("', '", $m);
			$bits[0]=stripslashes(preg_replace('/\'\s*\.\'/sm', '', $bits[0]));
			if (count($bits)==1) {
				$bits[1]='core';
			}
			$found[]=$bits;
			echo '<li>'.htmlspecialchars($bits[0]).'</li>';
		}
		echo '</ul>';
	}
}

findTranslations('');

echo '<h2>Newly added</h2>';
$_REQUEST['strings']=$found;
$added=Core_languagesAddStrings();
if (count($added)) {
	echo '<ul><li>'.join('</li><li>', $added).'</li></ul>';
}
else {
	echo 'none';
}

echo '<h2>wrong format</h2><ul><li>'
	.join('</li><li>', $wrong_format)
	.'</li></ul>';
