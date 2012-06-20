<?php
/**
	* provides information on a specific theme
	* paramaters that can be given:
	* theme        -       id of the theme
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

$id = addslashes(@$_GET[ 'theme' ]);

/**
 * get theme details from db
 */
$theme = dbRow(
	'select id, name, author, description, version, last_updated, author_url,'
	.'tags, rating from themes_api where moderated="yes" and id=' . $id
);

/**
 * theme was not found
 */
if ($theme == false) {
	die(__('not found'));
}
/**
 * add screenshot urls, html file urls and
 * convert author from id to name
 */
$author=dbOne(
	'select name from user_accounts where id=' . $theme[ 'author' ],
	'name'
);
$theme['author'] = $author;
$theme['short_description']=substr($theme['description'], 0, 26).' ...';
$theme['screenshot'] = ThemesApi_getScreenshot($id);
$theme['variants'] = ThemesApi_getVariants($id);

/**
 * add download link
 */
$theme[ 'download' ] = ThemesApi_downloadLink($id);
/**
 * encode and print data
 */
$theme = json_encode($theme);
die($theme);
