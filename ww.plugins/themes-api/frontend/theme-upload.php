<?php
/**
	* process themes which are uploaded via jquery/ajax
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

$session_id = @$_POST[ 'PHPSESSID' ];
session_id($session_id);
session_start();

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.incs/bb2html.php';

/**
 * check user is logged in
 */
$user_id = (int)@$_SESSION[ 'userdata' ][ 'id' ];
if ($user_id == 0) {
	Core_quit();
}

$name = reset(explode('.', $_FILES[ 'theme-zip' ][ 'name' ]));
$version = addslashes(@$_POST[ 'version' ]);
$description = bb2html(addslashes(@$_POST[ 'description' ]));
$tags = addslashes(@$_POST[ 'tags' ]);
$author = addslashes(@$_POST[ 'author' ]);
$author_url = addslashes(@$_POST[ 'author_url' ]);

$_SESSION[ 'theme_upload' ][ 'description' ] = $description;
$_SESSION[ 'theme_upload' ][ 'version' ] = $version;
$_SESSION[ 'theme_upload' ][ 'tags' ] = $tags;

/**
 * make sure all required fields are populated
 */
if ($version == '' || $author == '') {
	die( 'error' );
}
/**
 * make sure zip file is being used
 */
$theme_old_name = $_FILES[ 'theme-zip' ][ 'name' ];
$theme_old_name =end(explode('.', $theme_old_name));
if ($theme_old_name != 'zip') {
	die(__('must be a zip file'));
}

/**
 * add theme to database
 */
dbQuery(
	'insert into themes_api values("", "'
	. $name . '", "' . $author . '", "' . $description . '", "'
	. $version . '", "' . date('Y-m-d H:i:s') . '", "'
	. $author_url . '", "' . $tags . '", "no", "0")'
);

/**
 * make directory for files, according to
 * the id
 */
$last_id = dbLastInsertId();
$dir = USERBASE.'/f/themes_api/themes/';
if (!is_dir($dir . $last_id)) {
	mkdir($dir . $last_id);
}

/**
 * move file to user-files dir
 */
$new_file_name = $last_id . '.zip';
move_uploaded_file(
	$_FILES[ 'theme-zip' ][ 'tmp_name' ],
	$dir . $last_id . '/' . $new_file_name
);

echo $last_id;
?>
