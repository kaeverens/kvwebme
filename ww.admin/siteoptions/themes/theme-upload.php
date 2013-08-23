<?php
/**
  * facilitates the uploading of themes
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
$themes_personal = USERBASE.'/themes-personal/';
$temp_dir = USERBASE.'/themes-personal/temp_dir/';

// { Theme_findErrors

/**
  * checks themes for php files
  *
  * @param string $dir the directory to check
  *
  * @return mixed errors, or false if no errors
  */
function Theme_findErrors($dir) {
	if (!file_exists($dir) || !is_dir($dir)) {
		return false;
	}
	$files=new DirectoryIterator($dir);
	foreach ($files as $file) {
		if ($file->isDot()) {
			continue;
		}
		if ($file->isDir()) {
			$check=Theme_findErrors($dir.'/'.$file->getFilename());
			if ($check) {
				return $check;
			}
		}
		if (preg_match('/\.php(\.|$)/', $file->getFilename())) {
			return 'archive contains PHP files';
		}
	}
	return false;
}

// }
// { Theme_getFirstVariant

/**
  * find a variant
  *
  * @param string $dir the directory to search
  *
  * @return mixed the first variant it finds or false if no variants
  */
function Theme_getFirstVariant($dir) {
	$files = scandir($dir);
	foreach ($files as $file) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if (end(explode('.', $file)) == 'css') {
			return reset(explode('.', $file));
		}
	}
	return false;
}

// }

// { make sure post is set and files are uploaded
if (!isset($_POST[ 'install-theme' ]) && !isset($_POST[ 'upload-theme' ])
	|| !isset($_FILES[ 'theme-zip' ][ 'tmp_name' ])
	|| !filesize($_FILES[ 'theme-zip' ][ 'tmp_name' ])
) {
	echo '<script>parent.themes_dialog("<em>no theme uploaded. installation '
		.'failed</em>");</script>';
	Core_quit();
}
// }
// { make temporary dir and move uploaded file there
shell_exec('rm -rf ' . $temp_dir); // start fresh
shell_exec('mkdir ' . $temp_dir);
move_uploaded_file(
	$_FILES['theme-zip']['tmp_name'],
	$temp_dir . $_FILES['theme-zip']['name']
);
echo '<script>parent.themes_dialog("<p>unzipping archive</p>");</script>';
shell_exec('cd ' . $temp_dir . ' && unzip ' . $_FILES[ 'theme-zip' ][ 'name' ]);
$name = reset(explode('.', $_FILES[ 'theme-zip' ][ 'name' ]));
$theme_folder = $temp_dir . $name;
if (!file_exists($theme_folder)) { // argh... why do people do this?
	$files=new DirectoryIterator($temp_dir);
	mkdir($theme_folder);
	foreach ($files as $file) {
		$fname=$file->getFilename();
		if ($file->isDot() || $fname==$name.'.zip' || $fname==$name) {
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
	echo '<script>parent.themes_dialog("<p>Wordpress theme detected. Trying t'
		.'o convert.</p>");</script>';
	require 'convert-wordpress.php';
	shell_exec('rm -rf ' . $temp_dir);
}
elseif (file_exists($theme_folder.'/index.html')
	&& strpos(file_get_contents($theme_folder.'/index.html'), 'freecsstemplat')
) { // freecsstemplates.org
	echo '<script>parent.themes_dialog("<p>freecsstemplates.org theme detecte'
		.'d. Trying to convert.</p>");</script>';
	require 'convert-freecsstemplates.org.php';
}
else { // unknown format!
	echo '<script>parent.themes_dialog("<em>Unknown theme format. Failed to i'
		.'nstall!</em>");</script>';
	shell_exec('rm -rf ' . $temp_dir);
	Core_quit();
}
// }
// { if theme fails check, remove temp dir and throw error
$msg='';
if (!$failure_message) {
	$msg=Theme_findErrors($theme_folder);
}
if ($msg || $failure_message) {
	shell_exec('rm -rf ' . $temp_dir);
	echo '<script>parent.themes_dialog("<em>installation failed: '
		.$failure_message.$msg.'</em>");</script>';
	Core_quit();
}
// }
// { get variant
if (is_dir($theme_folder . '/cs')) {
	$variant = Theme_getFirstVariant($theme_folder . '/cs/');
}
// }
// { remove temp dir and extract to themes-personal
shell_exec('rm -rf '.$themes_personal.'/'.$name);
rename($temp_dir.'/'.$name, $themes_personal.'/'.$name);
shell_exec('rm -rf ' . $temp_dir);
if (isset($_POST[ 'install-theme' ])) {
	$DBVARS['theme'] = $name;
	if (isset($variant)) {
		$DBVARS[ 'theme_variant' ] = $variant;
	}
	Core_configRewrite();
	Core_cacheClear('pages');
}
// }
echo '<script>parent.document.location="/ww.admin/siteoptions.php?page=them'
	.'es&msg=Theme Uploaded";</script>';
