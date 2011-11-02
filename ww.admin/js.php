<?php
require_once '../ww.incs/basics.php';
$md5=preg_replace('/.*md5=/', '', $_SERVER['REQUEST_URI']);
if (strpos($md5, '..')!==false) {
	exit;
}

header('Cache-Control: max-age=2592000, public');
header('Expires-Active: On');
header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
header('Pragma:');
header('Content-type: text/javascript;');

echo file_get_contents(USERBASE.'/ww.cache/admin/'.$md5);
