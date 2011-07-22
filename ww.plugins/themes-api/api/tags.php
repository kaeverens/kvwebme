<?php
/**
	* searches for themes with the specified tags
	* paramaters that can be given:
	* tags		-       a comma seperated list of tags
	* count	-	the number of results to return
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

$tags_combined = addslashes(@$_GET[ 'tags' ]);
$tags = array();

/**
 * turn tags into array
 */
if (strstr($tags_combined, ',') == false) {
	$tags[ 0 ] = $tags_combined;
}
else {
	$tags = explode(',', $tags);
}
