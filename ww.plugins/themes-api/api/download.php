<?php

/**
 * api/download.php, KV-Webme Themes API
 *
 * downloads a file with the given id
 *
 * paramaters that can be given:
 *
 * download	-       should be boolean true
 * id	        -       id of theme to download
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * make sure api rules are being followed
 */
$recent = @$_GET[ 'download' ];
if( $recent != 'true' )
        exit;

/**
 * make sure id is present
 */
$id = ( int ) @$_GET[ 'id' ];
if( $id == 0 )
        exit;

/**
 * get theme info from db
 */
$theme = dbRow( 'select name,version,moderated from themes_api where id=' . $id );

/**
 * make sure theme exists
 */
if( $theme == false )
	exit;

/**
 * make sure theme has been moderated, if not
 * still let moderators download
 */
if( $theme[ 'moderated' ] == 'no' && ( !isset( $_SESSION[ 'userdata' ] ) && !isset( $_SESSION[ 'userdate' ][ 'groups' ][ 'moderators' ] ) ) )
	die( 'This theme is awaiting moderation and has not been deemed as safe yet.' );

/**
 * download file
 */
$file = USERBASE . 'f/themes_api/themes/' . $id . '/' . $id . '.zip';
header( 'Content-type: application/force-download' );
header( 'Content-Transfer-Encoding: Binary' );
header( 'Content-length: ' . filesize( $file ) );
header( 'Content-disposition: attachment; filename="' . $theme[ 'name' ] . '.zip"' );
readfile( $file );

?>
