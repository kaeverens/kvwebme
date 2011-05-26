<?php

/**
 * frontend/clean.php, KV-Webme Themes Repository
 *
 * cleans both the user files directory and the db if a user
 * exits the form half way through completion
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.plugins/themes-api/api/funcs.php';

/**
 * check user is logged in
 */
$user_id = ( int ) @$_SESSION[ 'userdata' ][ 'id' ];
if( $user_id == 0 )
	exit;

/**
 * make sure input is valid
 */
$id = ( int ) @$_POST[ 'id' ];
if( $id == 0 )
	exit;

echo $id;

//themes_api_error( "", $id );

?>
