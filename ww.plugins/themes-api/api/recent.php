<?php
/**
	* returns a number of recent themes, where
	* count is the number of themes to return,
	* 10 is the default value
	* paramaters that can be given:
	* recent       -       should be boolean true
	* count	-	number of themes to return, default = 10
	* start	-	offset to start query from, default = 0
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
$recent = @$_GET[ 'recent' ];
if ($recent != 'true') {
	Core_quit();
}

/**
 * check if count is defined
 */
$count = (int)@$_GET[ 'count' ];
if ($count == 0) {
	$count = 10;
}

/**
 * check if start is defined
 */
$start = (int)@$_GET[ 'start' ];

/**
 * get themes from db
 */
$themes=dbAll(
	'select id, name, author, description, version, last_updated, author_url,'
	.'tags, rating from themes_api where moderated="yes" order by last_updated'
	.' desc limit ' . $start . ', ' . $count
);

/**
 * die if error or empty
 */
if ($themes == false || count($themes) == 0) {
	die('none');
}

/**
 * add screenshots, html files and
 * download link to array
 */
for ($i = 0; $i < count($themes); ++$i) {

	$author = dbOne(
		'select name from user_accounts where id=' . $themes[ $i ][ 'author' ],
		'name'
	);
	$id = $themes[ $i ][ 'id' ];
	$themes[ $i ][ 'author' ] = $author;
	$themes[$i]['short_description']=substr($themes[$i]['description'], 0, 26)
		. ' ...';
	$themes[ $i ][ 'screenshot' ] = ThemesApi_getScreenshot($id);
	$themes[ $i ][ 'variants' ] = ThemesApi_getVariants($id);
	$themes[ $i ][ 'download' ] = ThemesApi_downloadLink($id);
}

$themes = ThemesApi_addDownloadCount($themes);

/**
 * json_encode and print results
 */
$themes = json_encode($themes);
die($themes);
