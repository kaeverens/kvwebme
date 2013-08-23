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
	* ThemesApi_getScreenshot
	*
	* @param int $id - id of theme
	*
	* @return the url of the theme screenshot
	*/
function ThemesApi_getScreenshot($id) {
	return ThemesApi_calculateUrl()
		.'/ww.plugins/themes-api/api.php?screenshot=true&id='.$id;
}
/**
	* returns an array of css files associated
	* with the theme
	*
	* @param int $id ID of the theme
	*
	* @return array variants
	*/
function ThemesApi_getVariants($id) {

	$variant_dir = USERBASE.'/f/themes_api/themes/'.$id.'/'.$id.'/cs/';
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
	* Accurately calculates the server URL
	*
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
	* dies with an error message, before doing so
	* it cleans the contents of the themes-api/extract
	* directory and removes the theme from the server
	* and the database
	*
	* @param string $msg the error message
	* @param int    $id  ID of a theme
	*
	* @return null
	*/
function ThemesApi_error($msg, $id = null) {
	/**
	 * remove temporary extract stuff
	 */
	shell_exec('rm -rf '.USERBASE.'/f/themes_api/extract/*');

	if ($id != 0) {

		/**
		 * remove theme from server
		 */
		shell_exec('rm -rf '.USERBASE.'/f/themes_api/themes/'.$id);

		/**
		 * remove theme from database
		 */
		dbQuery('delete from themes_api where id='.$id);

	}

	die($msg);
}
/**
	* return the download URL of a theme
	*
	* @param int $id ID of the theme
	*
	* @return string URL of the theme
	*/
function ThemesApi_downloadLink($id) {
	return ThemesApi_calculateUrl()
		.'/ww.plugins/themes-api/api.php?download=true&id='.$id;
}
/**
	* display an theme's screenshot
	*
	* @param string $file the screenshot
	*
	* @return null
	*/
function ThemesApi_displayImage($file) {
	if (!file_exists($file) || !filesize($file)) {
		die(__('File %1 does not exist', array($file), 'core'));
	}

	$arr=getimagesize($file);
	if ($arr[0]>240 || $arr[1]>172) {
		$md5=USERBASE.'/ww.cache/screenshots/'.md5($file).'.png';
		if (!file_exists($md5)) {
			@mkdir(USERBASE.'/ww.cache/screenshots');
			CoreGraphics::resize($file, $md5, 240, 172);
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
/**
	* get a theme using its ID for identification
	*
	* @param array $themes array of themes
	* @param int   $id     the ID to search for
	*
	* @return array the theme to return
	*/
function ThemesApi_getThemeFromId($themes, $id) {
	foreach ($themes as $theme) {
		if ($theme[ 'id' ] == $id) {
			return $theme;
		}
	}
}
/**
	* add download counts to an array of themes
	*
	* @param array $themes the array of themes
	*
	* @return array array of themes with download counts added
	*/
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
