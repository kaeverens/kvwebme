<?php
/**
	* given a short url, retrieves the longer version and redirects the browser
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'basics.php';

if (!isset($_GET['s'])) {
	Core_quit();
}
$s=addslashes($_GET['s']);
$l=dbOne("select long_url from short_urls where short_url='$s'", 'long_url');
if ($l) {
	header('Location: '.$l);
}
else {
	echo __('That url is obsolete, or incorrect');
}
