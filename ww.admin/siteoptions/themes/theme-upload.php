<?php
/**
 * ww.admin/siteoptions/themes/theme-upload.php, KV-Webme
 *
 * facilitates the uploading of themes
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @author     Kae Verens <kae@kvsites.ie>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * check_theme
 *
 * checks themes for php files
 */
function Theme_findErrors( $dir ) {
	$files = scandir( $dir );
	foreach( $files as $file ){
		if ( $file == '.' || $file == '..' ) {
			continue;
		}
		if ( is_dir( $file ) ) {
			$check=Theme_findErrors( $file );
			if ($check) {
				return $check;
			}
		}
		if ( preg_match('/\.php(\.|$)/', $file ) ) {
			return 'archive contains PHP files';
		}
	}
	return false;
}

/**
 * Theme_getFirstVariant
 *
 * returns the first variant it finds
 * false if no variants
 */
function Theme_getFirstVariant( $dir ) {
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

// { make sure post is set and files are uploaded
if( !isset( $_POST[ 'install-theme' ] ) && !isset( $_POST[ 'upload-theme' ] )
	|| !isset($_FILES[ 'theme-zip' ][ 'tmp_name' ])
	|| !filesize( $_FILES[ 'theme-zip' ][ 'tmp_name' ])
) {
	echo '<script>parent.themes_dialog("<em>no theme uploaded. installation '
		.'failed</em>");</script>';
	exit;
}
// }
// { make temporary dir and move uploaded file there
$themes_personal = USERBASE . 'themes-personal/';
$temp_dir = USERBASE . 'themes-personal/temp_dir/';
shell_exec( 'rm -rf ' . $temp_dir ); // start fresh
shell_exec( 'mkdir ' . $temp_dir );
move_uploaded_file( $_FILES[ 'theme-zip' ][ 'tmp_name' ], $temp_dir . $_FILES[ 'theme-zip' ][ 'name' ] );
echo '<script>parent.themes_dialog("<p>unzipping archive</p>");</script>';
shell_exec( 'cd ' . $temp_dir . ' && unzip ' . $_FILES[ 'theme-zip' ][ 'name' ] );
$name = reset( explode( '.', $_FILES[ 'theme-zip' ][ 'name' ] ) );
$theme_folder = $temp_dir . $name;
if (!file_exists($theme_folder)) { // argh... why do people do this?
	$files=new DirectoryIterator($temp_dir);
	mkdir($theme_folder);
	foreach ($files as $file) {
		if ($file->isDot() || $file->getFilename()==$name.'.zip') {
			continue;
		}
		rename($file->getPathname(), $theme_folder.'/'.$file->getFilename());
	}
}
// }
// { identify the theme format, and convert if necessary
$failure_message='';
if (file_exists($theme_folder.'/h') && file_exists($theme_folder.'/c')
	&& file_exists($theme_folder.'/screenshot.png')
) { // kvWebME format
	// nothing to do
}
else if (file_exists($theme_folder.'/index.php')
	&& file_exists($theme_folder.'/single.php')
) { // wordpress
	echo '<script>parent.themes_dialog("<p>Wordpress theme detected. Trying to convert.</p>");</script>';
	require 'convert-wordpress.php';
	shell_exec( 'rm -rf ' . $temp_dir );
}
else if (file_exists($theme_folder.'/index.html')
	&& strpos(file_get_contents($theme_folder.'/index.html'), 'freecsstemplates.org')
) { // freecsstemplates.org
	echo '<script>parent.themes_dialog("<p>freecsstemplates.org theme detected. Trying to convert.</p>");</script>';
	require 'convert-freecsstemplates.org.php';
//	shell_exec( 'rm -rf ' . $temp_dir );
}
else { // unknown format!
	echo '<script>parent.themes_dialog("<em>Unknown theme format. Failed to install!</em>");</script>';
//	shell_exec( 'rm -rf ' . $temp_dir );
	exit;
}
// }
// { if theme fails check, remove temp dir and throw error
$msg='';
if (!$failure_message) {
	$msg=Theme_findErrors( $theme_folder );
}
if( $msg || $failure_message ){
	shell_exec( 'rm -rf ' . $temp_dir );
	echo '<script>parent.themes_dialog("<em>installation failed: '.$failure_message.$msg.'</em>");</script>';
	exit;
}
// }
// { get variant
if ( is_dir( $theme_folder . '/cs' ) ) {
	$variant = Theme_getFirstVariant( $theme_folder . '/cs/' );
}
// }
// { remove temp dir and extract to themes-personal
shell_exec('rm -rf '.$themes_personal.'/'.$name);
rename($temp_dir.'/'.$name, $themes_personal.'/'.$name);
#shell_exec( 'cd ' . $themes_personal . ' && unzip -o temp_dir/' . $_FILES[ 'theme-zip' ][ 'name' ] );
shell_exec( 'rm -rf ' . $temp_dir );
if( isset( $_POST[ 'install-theme' ] ) ){
        $DBVARS['theme'] = $name;
	if( isset( $variant ) )
		$DBVARS[ 'theme_variant' ] = $variant;
        config_rewrite( );
        cache_clear( 'pages' );
}
// }
echo '<script>parent.document.location="/ww.admin/siteoptions.php?page=themes&msg=Theme Uploaded";</script>';
