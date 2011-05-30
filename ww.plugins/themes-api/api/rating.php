<?php

/**
 * api/rating.php, KV-Webme Themes API
 *
 * returns a number of highest rated themes, where
 * count is the number of themes to return,
 * 10 is the default value
 * * paramaters that can be given:
 *
 * rating       -       should be boolean true
 * count	-	number of themes to return, default = 10
 * start	-	offset to start query from, default = 0
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * make sure api rules are being followed
 */
$rating = @$_GET[ 'rating' ];
if( $rating != 'true' )
	exit;

/**
 * check if count is defined
 */
$count = ( int ) @$_GET[ 'count' ];
if( $count == 0 )
	$count = 10;

/**
 * check if start is defined
 */
$start = ( int ) @$_GET[ 'start' ];

/**
 * get themes from db
 */
$themes = dbAll( 'select id, name, author, description, version, last_updated, author_url, tags, rating from themes_api where moderated="yes" order by last_updated desc limit ' . $start . ', ' . $count );

/**
 * die if error or empty
 */
if( $themes == false || count( $themes ) == 0 )
	die( 'none' );

/**
 * add screenshots, html files and
 * download link to array
 */
for( $i = 0; $i < count( $themes ); ++$i ){

	$author = dbOne( 'select name from user_accounts where id=' . $themes[ $i ][ 'author' ], 'name' );
	$id = $themes[ $i ][ 'id' ];
	$themes[ $i ][ 'author' ] = $author;
	$themes[ $i ][ 'short_description' ] = substr( $themes[ $i ][ 'description' ], 0, 26 ) . ' ...';
	$themes[ $i ][ 'screenshot' ] = themes_api_get_screenshot( $id );
        $themes[ $i ][ 'variants' ] = themes_api_get_variants( $id );
	$themes[ $i ][ 'download' ] = themes_api_download_link( $id );
	$rating = ( isset( $ratings[ $themes[ $i ][ 'name' ] ] ) ) ?
		$ratings[ $themes[ $i ][ 'name' ] ] :
		0;
	
}

$ratings = dbAll( 
	'select name, avg( rating ) from ratings where type="theme" group by name order by rating desc'
);


$reorder = array( );
$i = 0;
foreach( $ratings as $rating ){
	$id = end( explode( '_', $rating[ 'name' ] ) );
	$theme = themes_api_get_theme_from_id( $themes, $id );
	if( $theme != '' ){
		$reorder[ $i ] = $theme;
		++$i;
	}
}

foreach( $themes as $theme ){
	if( themes_api_get_theme_from_id( $reorder, $theme[ 'id' ] ) == '' )
		array_push( $reorder, $theme );
}

/**
 * json_encode and print results
 */
$themes = json_encode( $reorder );
die( $themes );
