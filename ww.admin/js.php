<?php
/**
	* load up a JS file
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
	header('HTTP/1.0 304 Not Modified');
	die;
}

require_once '../ww.incs/basics.php';
if (strpos($_SERVER['REQUEST_URI'], '=')!==false) {
	$md5=preg_replace('/.*md5=/', '', $_SERVER['REQUEST_URI']);
	$blah=0;
}
else {
	$md5=preg_replace('/.*.php/', '', $_SERVER['REQUEST_URI']);
	$blah=1;
}
if (strpos($md5, '..')!==false) {
	Core_quit();
}

$fname=USERBASE.'/ww.cache/admin/'.$md5;
header('X-Powered-By:');
header("Expires: ".gmdate("D, d M Y H:i:s", filemtime($fname)+216000)." GMT");
header("Cache-Control: max-age=216000, private, must-revalidate", true);
header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($fname))." GMT");
header('Set-Cookie:');
header('Pragma:');
header('Content-Type: application/x-javascript; charset=utf-8');

readfile($fname);
