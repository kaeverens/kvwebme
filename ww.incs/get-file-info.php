<?php
/**
	* retrieve info about a file from the users' repository and send it to the browser
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
if (!isset($_REQUEST['src'])) {
	exit;
}
$file=USERBASE.$_REQUEST['src'];
if (strpos($file, '..')!==false
	|| (strpos($file, '/.')!==false
	&& strpos(preg_replace('#/\.files/#', '/', $file), '/.')!==false)
) {
	exit;
}
if (!file_exists($file) || !is_file($file)) {
	header('HTTP/1.0 404 Not Found');
	echo 'file does not exist';
	exit;
}

$finfo=finfo_open(FILEINFO_MIME_TYPE);
$mime=finfo_file($finfo, $file);

echo json_encode(array(
	'mime'=>$mime
));
