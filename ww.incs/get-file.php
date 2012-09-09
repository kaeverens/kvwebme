<?php
/**
	* retrieve a file from the users' repository and send it to the browser
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
if (!isset($_REQUEST['filename'])) {
	Core_quit();
}
$file=USERBASE.'/f/'.$_REQUEST['filename'];
if (strpos($file, '..')!==false
	|| (strpos($file, '/.')!==false
	&& strpos(preg_replace('#/\.files/#', '/', $file), '/.')!==false)
) {
	Core_quit();
}
if (!file_exists($file) || !is_file($file)) {
	header('HTTP/1.0 404 Not Found');
	echo __('File does not exist');
	Core_quit();
}

foreach ($PLUGINS as $plugin) {
	if (!isset($plugin['frontend']['file_hook'])
		|| !function_exists($plugin['frontend']['file_hook'])
	) {
		continue;
	}
	$plugin['frontend']['file_hook'](array(
		'requested_file'=>'/'.$_REQUEST['filename']
	));
}

$force_download=isset($_REQUEST['force_download']);
header('Content-Description: File Transfer');
if ($force_download) {
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($file));
}
else {
	header('Content-Type: '.MimeType_get(preg_replace('/.*\./', '', $file)));
}
header('Content-Transfer-Encoding: binary');
if ($force_download) {
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
}
else{
	header('Expires-Active: On');
	if (!isset($DBVARS['f_cache']) || $DBVARS['f_cache']=='0') {
		header('Cache-Control: max-age = 9999999999');
		header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	}
	else {
		$seconds=3600*$DBVARS['f_cache'];
		header('Cache-Control: max-age = '.$seconds);
		header('Expires: '. date('r', time()+$seconds));
	}
	header('Pragma:');
}
header('Content-Length: ' . filesize($file));
header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($file))." GMT");
ob_clean();
flush();
readfile($file);

/**
	* returns a MieType when given a filename
	*
	* @param string $f filename
	*
	* @return string
	*/
function MimeType_get($f) {
	$mimetypes = array(
		'doc'=>'application/msword', 'exe'=>'application/octet-stream',
		'pdf'=>'application/pdf', 'xls'=>'application/vnd.ms-excel',
		'ppt'=>'application/vnd.ms-powerpoint', 'js'=>'application/x-javascript',
		'sh'=>'application/x-sh', 'swf'=>'application/x-shockwave-flash',
		'tar'=>'application/x-tar', 'xhtml'=>'application/xhtml+xml',
		'zip'=>'application/zip', 'mid'=>'audio/midi', 'midi'=>'audio/midi',
		'mp3'=>'audio/mpeg', 'ram'=>'audio/x-pn-realaudio',
		'rm'=>'audio/x-pn-realaudio', 'wav'=>'audio/x-wav',
		'bmp'=>'image/bmp', 'gif'=>'image/gif', 'jpeg'=>'image/jpeg',
		'jpg'=>'image/jpeg', 'jpe'=>'image/jpeg', 'png'=>'image/png',
		'tiff'=>'image/tiff', 'tif'=>'image/tiff', 'djvu'=>'image/vnd.djvu',
		'css'=>'text/css', 'html'=>'text/html', 'htm'=>'text/html',
		'txt'=>'text/plain', 'rtf'=>'text/rtf',
		'tsv'=>'text/tab-separated-values', 'xml'=>'text/xml',
		'mpeg'=>'video/mpeg', 'mpg'=>'video/mpeg',
		'qt'=>'video/quicktime', 'mov'=>'video/quicktime',
		'avi'=>'video/x-msvideo'
	);
	$extension = preg_replace('/.*\./', '', $f);
	if (isset($mimetypes[$extension])) {
		return $mimetypes[$extension];
	}
	return 'unknown/mimetype';
}
Core_flushBuffer('file');
