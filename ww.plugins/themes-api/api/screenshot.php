<?php

/**
 * api/screenshot.php, KV-Webme Themes API
 *
 * displays a screenshot on the screen 
 *
 * parameters that can be given:
 *
 * screenshot	-       should be boolean true
 * id		-	id of theme
 * variant      -       name of variant screenshot to display
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @author     Kae Verens <kae@verens.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * make sure api rules are being followed
 */
$recent = @$_GET[ 'screenshot' ];
if( $recent != 'true' )
        exit;

/**
 * check if id is defined
 */
$id = ( int ) @$_GET[ 'id' ];
if( $id == 0 )
	exit;

$theme_dir = USERBASE . 'f/themes_api/themes/' . $id . '/' . $id . '/';

/**
 * check if variant is defined
 * if not display theme screenshot
 */
$variant = @$_GET[ 'variant' ];
if( $variant == '' || !is_dir( $theme_dir . 'cs' ) ){
	themes_api_display_image( $theme_dir . 'screenshot.png' );
	exit;
}

/**
 * loop through theme dir
 */
$handler = opendir( $theme_dir . 'cs' );
while( $file = readdir( $handler ) ){

	if( $file == '.' && $file == '..' )
		continue;

	/**
	 * get file extention
	 */
	$name = explode( '.', $file );
	$ext = end( $name );
	$name = reset( $name );

	if( $ext == 'png' && $name == $variant ){
		themes_api_display_image( $theme_dir . 'cs/' . $name . '.' . $ext );
		break;
	}

}
closedir( $handler );
