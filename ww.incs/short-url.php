<?php
/**
	* given a short url, retrieves the longer version and redirects the browser
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     http://webworks.ie/
	*/

require 'basics.php';

if (!isset($_GET['s'])) {
	exit;
}
$s=addslashes($_GET['s']);
$l=dbOne("select long_url from short_urls where short_url='$s'", 'long_url');
if ($l) {
	header('Location: '.$l);
}
else {
	echo 'that url is obsolete, or incorrect';
}
