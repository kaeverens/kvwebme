<?php

/**
 * api/theme.php, KV-Webme Themes API
 *
 * provides information on a specific theme
 *
 * paramaters that can be given:
 *
 * theme        -       id of the theme
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$id = addslashes( @$_GET[ 'theme' ] );

/**
 * get theme details from db
 */
$theme = dbRow( 'select id, name, author, description, version, last_updated, author_url, tags, rating from themes_api where moderated="yes" and id=' . $id );

/**
 * theme was not found
 */
if( $theme == false )
	die( 'not found' );

/**
 * add screenshot urls, html file urls and
 * convert author from id to name
 */
$author = dbOne( 'select name from user_accounts where id=' . $theme[ 'author' ], 'name' );
$theme[ 'author' ] = $author;
$theme[ 'short_description' ] = substr( $theme[ 'description' ], 0, 26 ) . ' ...';
$theme[ 'screenshot' ] = themes_api_get_screenshot( $id );
$theme[ 'variants' ] = themes_api_get_variants( $id );

/**
 * add download link
 */
$theme[ 'download' ] = themes_api_download_link( $id );
/**
 * encode and print data
 */
$theme = json_encode( $theme );
die( $theme );

?>
