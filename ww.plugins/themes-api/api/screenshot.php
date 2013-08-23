<?php
/**
	* displays a screenshot on the screen 
	* parameters that can be given:
	* screenshot	-       should be boolean true
	* id		-	id of theme
	* variant      -       name of variant screenshot to display
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

/**
 * make sure api rules are being followed
 */
$recent = @$_GET[ 'screenshot' ];
if ($recent != 'true') {
	Core_quit();
}

/**
 * check if id is defined
 */
$id = (int)@$_GET[ 'id' ];
if ($id == 0) {
	Core_quit();
}

$theme_dir = USERBASE.'/f/themes_api/themes/' . $id . '/' . $id . '/';

/**
 * check if variant is defined
 * if not display theme screenshot
 */
$variant = @$_GET[ 'variant' ];
if ($variant == '' || !is_dir($theme_dir . 'cs')) {
	ThemesApi_displayImage($theme_dir . 'screenshot.png');
	Core_quit();
}

/**
 * loop through theme dir
 */
$handler = opendir($theme_dir . 'cs');
while ($file = readdir($handler)) {

	if ($file == '.' && $file == '..') {
		continue;
	}

	/**
	 * get file extention
	 */
	$name = explode('.', $file);
	$ext = end($name);
	$name = reset($name);

	if ($ext == 'png' && $name == $variant) {
		ThemesApi_displayImage($theme_dir . 'cs/' . $name . '.' . $ext);
		break;
	}
}
closedir($handler);
