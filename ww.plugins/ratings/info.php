<?php
/**
	* info.php, KV-Webme Ratings Plugin
	* echos info about a certain product
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../ww.incs/basics.php';

// { validate input
$name = addslashes(@$_GET[ 'name' ]);
if ($name == '') {
	exit;
}
// }
$votes = dbAll('select * from ratings where name="' . $name . '"');
$votes = count($votes);

echo $votes . ' people have rated this.';
exit;
