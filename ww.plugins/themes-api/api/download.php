<?php
/**
	* api/download.php, KV-Webme Themes API
	* downloads a file with the given id
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
 * make sure api rules are being followed
 */
$recent = @$_GET[ 'download' ];
if ($recent != 'true') {
	Core_quit();
}

/**
 * make sure id is present
 */
$id = ( int ) @$_GET[ 'id' ];
if ($id == 0) {
	Core_quit();
}

/**
 * get theme info from db
 */
$theme = dbRow(
	'select name,version,moderated from themes_api where id=' . $id
);

/**
 * make sure theme exists
 */
if ($theme == false) {
	Core_quit();
}

/**
 * make sure theme has been moderated, if not
 * still let moderators download
 */
if ($theme['moderated'] == 'no' && !isset($_SESSION['userdata'])) {
	die(
		__('This theme is awaiting moderation and has not been deemed as safe yet.')
	);
}

// save in database
$referrer = @$_SERVER[ 'HTTP_REFERER' ];
$ip = @$_SERVER[ 'REMOTE_ADDR' ];
dbQuery(
	'insert into themes_downloads values("",' . $id . ',"'
	. $referrer . '","' . $ip . '",now())'
);

/**
 * download file
 */
$file = USERBASE.'/f/themes_api/themes/' . $id . '/' . $id . '.zip';
header('Content-type: application/force-download');
header('Content-Transfer-Encoding: Binary');
header('Content-length: '.filesize($file));
header('Content-disposition: attachment; filename="'.$theme['name'].'.zip"');
readfile($file);
