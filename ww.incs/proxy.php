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
 * url 		-	the url to load
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require 'basics.php';
require SCRIPTBASE . 'ww.admin/admin_libs.php';

$url = @$_GET [ 'url' ];

/**
 * make sure host and referer are the same
 */
$referer = @$_SERVER[ 'HTTP_REFERER' ];
$referer = preg_replace( '/^https?:\/\/([^\/]*)\/.*/', '\1', $referer );
$host = $_SERVER[ 'SERVER_NAME' ];

if( $host != $referer )
	exit;

/**
 * get rest of query string and pass to url
 */
if( strpos( $url, '?' ) !== 0 ){
	$querystring = $_SERVER[ 'QUERY_STRING' ];
	$querystring = '?' . end( explode( '?', $querystring ) );
	$url = reset( explode( '?', $url ) ) . $querystring;
}

$content = curl( $url );

echo $content;
?>
