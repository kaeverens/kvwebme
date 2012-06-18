<?php
/**
	* plugin file for the themes api
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { configuration
/**
 * plugin array, which holds the configuartion
 * options for the plugin
 */
$plugin = array( 
	'name'=>function() {
		return __('Themes API');
	},
	'version'	=>	3,
	'description'=>function() {
		return __('Private Plugin for themes API');
	},
	'hide_from_admin'=>	true,
	'admin'		=>	array(
		'menu'	=>	array(
			'Themes Repository'	=>	'plugin.php?_plugin=themees-api&amp;_page=index'
		),
		'page_type'	=>	array( 
			'Themes Catalogue' => 'ThemesApi_catalogueAdmin',
			'Upload Form' => 'ThemesApi_admin'
		),
	),
		'frontend'	=>	array(
		'page_type'	=>	array(
			'Themes Catalogue' => 'ThemesApi_catalogueFrontend',
			'Upload Form'	=>	'ThemesApi_frontend',
		),
		'file_hook'	=>	'ThemesApi_filesCheck'
	)
);
/** translatable strings
	* __('Themes Repository')
	* __('Themes Catalogue')
	* __('Upload Form')
	* __('Upload Form')
	*/
// }

/**
	* show front-end page type for uploading themes
	*
	* @return html
	*/
function ThemesApi_frontend() {
	require SCRIPTBASE . 'ww.plugins/themes-api/frontend/upload.php';
	return $html;
}

/**
	* what's the point of this?
	*
	* @return html
	*/
function ThemesApi_admin() {
	echo __('this is the admin area page type');
}

/**
	* check uploaded file to see if it's acceptable
	*
	* @param array $vars list of parameters
	*
	* @return html
	*/
function ThemesApi_filesCheck( $vars ) {
	/**
	 * check if this file should be handled
	 * by this plugin
	 */
	$file = explode('/', $vars[ 'requested_file' ]);
	$dir = $file[ 1 ];
	if ( $file[ 1 ] != 'themes_api' ) {
		return true;
	}

	/**
	 * if you are a moderator, then you can download
	 */

	$id = $file[ 3 ];
	$moderated = dbOne(
		'select moderated from themes_api where id=' . $id,
		'moderated'
	);

	if ( $moderated == 'no' ) {
		die(__('This theme is awaiting moderation and has not been deemed as safe yet.'));
	}

	// save in database
	$referrer = @$_SERVER[ 'HTTP_REFERER' ];
	$ip = @$_SERVER[ 'REMOTE_ADDR' ];
	dbQuery(
		'insert into themes_downloads values("",' . $id . ',"'
		. $referrer . '","' . $ip . '",now())'
	);
}

/**
	* is this necessary??
	*
	* @return html
	*/
function ThemesApi_catalogueAdmin() {
	echo '<h1>'.__('Themes Repository').'</h1>';
}

/**
	* get catalogue of themes for front-end
	*
	* @return html
	*/
function ThemesApi_catalogueFrontend() {
	require SCRIPTBASE . 'ww.plugins/themes-api/frontend/catalogue.php';
	return $html;
}
