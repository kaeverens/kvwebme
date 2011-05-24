<?php

/**
 * plugin.php, KV-Webme Ratings Plugin
 *
 * saves the rating to the database
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../ww.incs/basics.php';

// { get and validate get data
$name = addslashes( @$_GET[ 'name' ] );
$type = addslashes( @$_GET[ 'type' ] );
$rating = ( int ) @$_GET[ 'rating' ];
$user = ( @$_SESSION[ 'userdata' ][ 'id' ] == 0 ) ?
	$_SESSION[ 'userdata' ][ 'id' ] :
	$_SESSION[ 'REMOTE_ADDR' ];
$date = date( 'm-d-Y' );

if( $rating == '' || $name == '' )
	die( 'error' );

// }

// { add item to db, or update existing item
dbQuery( 'insert into ratings values(
	"",
	"' . $name . '",
	"' . $rating . '",
	"' . $type . '",
	"' . $date . '",
	"' . $user . '"
)' );
// }

?>
