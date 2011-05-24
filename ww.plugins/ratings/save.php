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

// { get and validate post data
$name = addslashes( @$_POST[ 'name' ] );
$type = addslashes( @$_POST[ 'type' ] );
$rating = ( int ) @$_POST[ 'rating' ];
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
