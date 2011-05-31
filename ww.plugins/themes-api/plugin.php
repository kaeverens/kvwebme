<?php

/**
 * plugin.php, KV-Webme Themes Repository
 *
 * plugin file for the themes api
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    3.0
 */

/**
 * plugin array, which holds the configuartion
 * options for the plugin
 */
$plugin = array( 

	'name'		=>	'Themes API',
	'version'	=>	3,
	'description'	=>	'Private Plugin for themes API',
	'hide_from_admin'=>	true,
	'admin'		=>	array(
                'menu'	=>	array(
			'Themes Repository'	=>	'index'
                ),
		'page_type'	=>	array( 
			'Themes Catalogue' => 'themes_api_catalogue_admin',
			'Upload Form' => 'themes_api_admin'
		),
	),
        'frontend'	=>	array(
		'page_type'	=>	array(
			'Themes Catalogue' => 'themes_api_catalogue_frontend',
                	'Upload Form'	=>	'themes_api_frontend',
		),
		'file_hook'	=>	'themes_api_files_check'
        )
);

function themes_api_frontend( ){

	require SCRIPTBASE . 'ww.plugins/themes-api/frontend/upload.php';

	return $html;

}

function themes_api_admin( ){

	echo 'this is the admin area page type';

}

function themes_api_files_check( $vars ){

	/**
	 * check if this file should be handled
	 * by this plugin
	 */
	$file = explode( '/', $vars[ 'requested_file' ] );
	$dir = $file[ 1 ];
	if( $file[ 1 ] != 'themes_api' )
		return true;

	/**
	 * if you are a moderator, then you can download
	 */
	//if( isset( $_SESSION[ 'userdata' ] ) && isset( $_SESSION[ 'userdata' ][ 'groups' ][ 'moderators' ] ) )
		//return true; 

	$id = $file[ 3 ];
	$moderated = dbOne( 'select moderated from themes_api where id=' . $id, 'moderated' );

	if( $moderated == 'no' )
		die( 'This theme is awaiting moderation and has not been deemed as safe yet.' );

	// save in database
	$referrer = @$_SERVER[ 'HTTP_REFERER' ];
	$ip = @$_SERVER[ 'REMOTE_ADDR' ];
	dbQuery( 'insert into themes_downloads values("",' . $id . ',"'
		. $referrer . '","' . $ip . '",now())');

}

function themes_api_catalogue_admin( ){

	echo '<h1>Themes Repository</h1>';

}

function themes_api_catalogue_frontend( ){

	require SCRIPTBASE . 'ww.plugins/themes-api/frontend/catalogue.php';

	return $html;
}
