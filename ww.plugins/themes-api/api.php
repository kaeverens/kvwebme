<?php
/**
	* api.php, KV-Webme Themes API
	* provides information on themes in the repository
	* paramaters that can be given to the api:
	* theme	-	id of the theme						//working
	* tags		-	comma seperated, search by keywords
	* rated	-	by highest rated
	* count	-	int, number of themes to return
	* recent	-	if set as true will return recently added themes	//working
	* downloads	-	most downloaded
	* name		-	search by name						//working
	* start	- 	int, start searching themes at this position
	* download	-	if set to true will download a file with the id provided //working
	* id		-	int, id of theme					//working
	* screenshot	-	if set to true will display screenshoot			//working
	* variant 	-	will display screenshot of a particular variant		//working
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.plugins/themes-api/api/funcs.php';

if (!empty ($_REQUEST['theme'])) {
	require SCRIPTBASE . 'ww.plugins/themes-api/api/theme.php';
	Core_quit();
}
if (!empty ($_REQUEST['screenshot'])) {
	require SCRIPTBASE . 'ww.plugins/themes-api/api/screenshot.php';
	Core_quit();
}
if (!empty ($_REQUEST['tags'])) {
        require SCRIPTBASE . 'ww.plugins/themes-api/api/tags.php';
	Core_quit();
}
if (!empty ($_REQUEST['recent'])) {
        require SCRIPTBASE . 'ww.plugins/themes-api/api/recent.php';
        Core_quit();
}
if (!empty ($_REQUEST['rating'])) {
        require SCRIPTBASE . 'ww.plugins/themes-api/api/rating.php';
        Core_quit();
}
if (!empty ($_REQUEST['downloads'])) {
        require SCRIPTBASE . 'ww.plugins/themes-api/api/downloads.php';
        Core_quit();
}
if (!empty ($_REQUEST['name'])) {
        require SCRIPTBASE . 'ww.plugins/themes-api/api/name.php';
        Core_quit();
}
if (!empty($_REQUEST['download'])) {
	require SCRIPTBASE . 'ww.plugins/themes-api/api/download.php';
	Core_quit();
}
