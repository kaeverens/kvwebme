<?php

/**
 * api/tags.php, KV-Webme Themes API
 *
 * searches for themes with the specified tags
 *
 * paramaters that can be given:
 *
 * tags		-       a comma seperated list of tags
 * count	-	the number of results to return
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$tags_combined = addslashes( @$_GET[ 'tags' ] );
$tags = array( );

/**
 * turn tags into array
 */
if( strstr( $tags_combined, ',' ) == false )
	$tags[ 0 ] = $tags_combined;
else
	$tags = explode( ',', $tags );


?>
