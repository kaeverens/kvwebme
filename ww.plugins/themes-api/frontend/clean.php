<?php
/**
	* cleans the user files directory and db if a user
	* exits the form half way through completion
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

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.plugins/themes-api/api/funcs.php';

/**
 * check user is logged in
 */
$user_id = (int)@$_SESSION[ 'userdata' ][ 'id' ];
if ($user_id == 0) {
	Core_quit();
}
/**
 * make sure input is valid
 */
$id = (int) @$_POST[ 'id' ];
if ($id == 0) {
	Core_quit();
}

echo $id;
