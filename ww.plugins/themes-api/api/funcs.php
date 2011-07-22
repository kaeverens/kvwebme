<?php
/**
	* function library for the api
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
 * api/funcs.php, KV-Webme Themes API
 *
 * function library for the api
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * ThemesApi_getScreenshot
 *
 * @param $id - id of theme
 *
 * returns the url of the theme screenshot
 */
function ThemesApi_getScreenshot($id){
	return ThemesApi_calculateUrl()
		.'/ww.plugins/themes-api/api.php?screenshot=true&id='.$id;
}

/**
 * ThemesApi_getVariants
 * 
 * returns an array of css files associated
 * with the theme
 */
function ThemesApi_getVariants($id){

	$variant_dir = USERBASE.'f/themes_api/themes/'.$id.'/'.$id.'/cs/';
	$variants = array();

	/**
	 * if the dir doesn't exist return empty array
	 */
	if (!is_dir($variant_dir)) {
		return $variants;
	}

	/**
	 * loop through theme dir
	 */
	$handler = opendir($variant_dir);
	while ($file = readdir($handler)) {

		if ($file == '.' || $file == '..') {
			continue;
		}

		/**
		 * get file extention
		 */
		$name = explode('.', $file);
		$ext = end($name);

		if ($ext == 'css') {
			$name = reset($name);
			array_push($variants, $name);
		}

	}
	closedir($handler);

	return $variants;

}

/**
 * ThemesApi_calculateUrl
 *
 * Accurately calculates the server URL
 * 
 * @access public
 * @return string
 */
function ThemesApi_calculateUrl() {
	$url = 'http';

	if (@$_SERVER[ 'HTTPS' ] == 'on') {
		$url .= 's';
	}

	$url .= '://'.$_SERVER[ 'SERVER_NAME' ];

	if ($_SERVER[ 'SERVER_PORT' ] != '80') {
		$url .= ':'.$_SERVER[ 'SERVER_PORT' ];
	}

	return $url;
}

/**
 * ThemesApi_error
 * 
 * dies with an error message, before doing so
 * it cleans the contents of the themes-api/extract
 * directory and removes the theme from the server
 * and the database 
 */
function ThemesApi_error($msg, $id = null){
	/**
	 * remove temporary extract stuff
	 */
	shell_exec('rm -rf '.USERBASE.'f/themes_api/extract/*');

	if ($id != 0) {

		/**
		 * remove theme from server
		 */
		shell_exec('rm -rf '.USERBASE.'f/themes_api/themes/'.$id);

		/**
		 * remove theme from database
		 */
		dbQuery('delete from themes_api where id='.$id);

	}

	die($msg);
}

/**
 * ThemesApi_downloadLink
 *
 * given the id and name of the theme this function
 * will return the download URL
 */
function ThemesApi_downloadLink($id){
	return ThemesApi_calculateUrl()
		.'/ww.plugins/themes-api/api.php?download=true&id='.$id;
}

/**
 * ThemesApi_displayImage
 *
 * display an image to the screen
 */
function ThemesApi_displayImage($file){

	if (!file_exists($file) || !filesize($file)) {
		die('file '.$file.' does not exist');
	}

	$arr=getimagesize($file);
	if ($arr[0]>240 || $arr[1]>172) {
		$md5=USERBASE.'/ww.cache/screenshots/'.md5($file).'.png';
		if (!file_exists($md5)) {
			@mkdir(USERBASE.'/ww.cache/screenshots');
			`convert $file -resize 240x172 $md5`;
		}
		$file=$md5;
	}

	/**
	 * set headers and read file
	 */
	header('Content-type: image/png');
	header('Content-Transfer-Encoding: Binary');
	header('Content-length: '.filesize($file));
	readfile($file);
}

function ThemesApi_getThemeFromId($themes, $id) {
	foreach ($themes as $theme) {
		if ($theme[ 'id' ] == $id) {
			return $theme;
		}
	}
}

function ThemesApi_addDownloadCount($themes) {
	$ids = array();
	foreach ($themes as $theme) {
		array_push($ids, $theme[ 'id' ]);
	}

	$downloads = dbAll(
		'select count(id),theme from themes_downloads where theme='
		. implode(' or theme=', $ids).' group by theme'
	);

	for ($i = 0; $i < count($themes); ++$i) {
		foreach ($downloads as $download) {
			if ($download[ 'theme' ] == $themes[ $i ][ 'id' ]) {
				$themes[ $i ][ 'downloads' ] = $download[ 'count(id)' ];
			}
		}
		if (!isset($themes[ $i ][ 'downloads' ])) {
			$themes[ $i ][ 'downloads' ] = 0;
		}
	}
	
	return $themes;
}
