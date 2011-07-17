<?php
/**
  * script for retrieving an image, optionally resizing it
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$uri=@$_GET['uri'];
if ($uri=='') {
	die('no image');
}

if (strpos($uri, ',')!==false) { // width and height
	$uri=explode(',', $uri);
	$width=explode('=', $uri[1]);
	$width=end($width);
	$height=explode('=', $uri[2]);
	$height=end($height);
	$uri=$uri[0];
}
if (strpos($uri, 'http://')===false) {
	if (preg_match('#\.\.|/\.#', $uri)) { // illegal filename
		die('no! /bad/ hacker!');
	}
	$loc=USERBASE.'f/'.$uri;
	if (!file_exists($loc)) {
		header('HTTP/1.0 404 Not Found');
		echo 'file '.$loc.' not found';
		exit;
	}
	$uri=$loc;
}

$image=new Image($uri, true);
if (isset($width)&&isset($height)) {
	$image->resize($width, $height);
}

$image->display();
