<?php

/**
 * ww.admin/siteoptions/themes/theme-upload.php, KV-Webme
 *
 * facilitates the uploading of themes
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * check_theme
 *
 * checks themes for php files
 */
function check_theme( $dir ){

	$files = scandir( $dir );

	foreach( $files as $file ){

		if( $file == '.' || $file == '..' )
			continue;

		if( is_dir( $file ) && !check_theme( $file ) )
			return false;

		if( preg_match('/\.php(\.|$)/', $file ) )
			return false;

	}

	return true;

}

/**
 * get_first_variant
 *
 * returns the first variant it finds
 * false if no variants
 */
function get_first_variant( $dir ){

	$files = scandir( $dir );

	foreach( $files as $file ){

		if( $file == '.' || $file == '..' )
			continue;

		if( end( explode( '.', $file ) ) == 'css' )
			return reset( explode( '.', $file ) );

	}

	return false;

}

require '../../../ww.incs/basics.php';

/**
 * make sure post is set
 */
if( !isset( $_POST[ 'install-theme' ] ) && !isset( $_POST[ 'upload-theme' ] ) )
	exit;

/**
 * make sure uploaded file exists
 */
if( !isset( $_FILES[ 'theme-zip' ][ 'tmp_name' ] ) )
	exit;

/**
 * make temporary dir and move uploaded file there
 */
$themes_personal = USERBASE . 'themes-personal/';
$temp_dir = USERBASE . 'themes-personal/temp_dir/';

shell_exec( 'mkdir ' . $temp_dir );
move_uploaded_file( $_FILES[ 'theme-zip' ][ 'tmp_name' ], $temp_dir . $_FILES[ 'theme-zip' ][ 'name' ] );
shell_exec( 'cd ' . $temp_dir . ' && unzip -o ' . $_FILES[ 'theme-zip' ][ 'name' ] );

$name = reset( explode( '.', $_FILES[ 'theme-zip' ][ 'name' ] ) );
$theme_folder = $temp_dir . $name;

/**
 * get variant
 */
if( is_dir( $theme_folder . '/cs' ) )
	$variant = get_first_variant( $theme_folder . '/cs/' );

/**
 * if theme fails check, remove temp dir and
 * throw error
 */
if( !check_theme( $theme_folder ) ){
	shell_exec( 'rm -rf ' . $temp_dir );
	header( 'location: /ww.admin/siteoptions.php?page=themes&uploaded=false' );
}

/**
 * remove temp dir and extract to themes-personal
 */
shell_exec( 'cd ' . $themes_personal . ' && unzip -o temp_dir/' . $_FILES[ 'theme-zip' ][ 'name' ] );
shell_exec( 'rm -rf ' . $temp_dir );

if( isset( $_POST[ 'install-theme' ] ) ){
        $DBVARS['theme'] = $name;

	if( isset( $variant ) )
		$DBVARS[ 'theme_variant' ] = $variant;

        config_rewrite( );
        cache_clear( 'pages' );
}

header( 'location: /ww.admin/siteoptions.php?page=themes&uploaded=true' );
?>
