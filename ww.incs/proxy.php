<?php
/**
  * ww.incs/proxy.php, KV-Webme
  *
  * works as a proxy for accessing other web
  * servers, only works when refered to internally
  *
  * the querystring is parsed so if a url like this is entered:
  *
  * http://kvweb.me/ww.plugins/themes-api/api.php?recent=true&count=3&start=0
  *
  * then all parameters will be passed to the loaded url
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
  * @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'basics.php';
require SCRIPTBASE . 'ww.admin/admin_libs.php';

$url = @$_GET[ 'url' ];

/**
 * make sure host and referer are the same
 */
$referer = @$_SERVER[ 'HTTP_REFERER' ];
$referer = preg_replace('/^https?:\/\/([^\/]*)\/.*/', '\1', $referer);
$host = $_SERVER[ 'SERVER_NAME' ];
if ($host != $referer) {
	Core_quit();
}

/**
 * get rest of query string and pass to url
 */
if (strpos($url, '?') !== 0) {
	$querystring = $_SERVER[ 'QUERY_STRING' ];
	$explode=explode('?', $querystring);
	$querystring = '?'.end($explode);
	$explode=explode('?', $url);
	$url = reset($explode) . $querystring;
}

$content = Core_getExternalFile($url);

echo $content;
?>
