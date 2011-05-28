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
$user = ( ( int ) @$_SESSION[ 'userdata' ][ 'id' ] == 0 ) ?
	$_SERVER[ 'REMOTE_ADDR' ] :
	$_SESSION[ 'userdata' ][ 'id' ];
$date = date( 'm-d-Y' );

if( $name == '' )
	die( 'error' );

// }

// { make sure user hasn't already voted
$query = dbRow( 'select id from ratings'
	. ' where user="' . $user . '"'
	. ' and name="' . $name . '"'
);
// }

// { add item to db, or update existing item
if( $query == false ){
	dbQuery( 'insert into ratings values(
		"",
		"' . $name . '",
		"' . $rating . '",
		"' . $type . '",
		"' . $date . '",
		"' . $user . '"
	)' );
	die( 'insert' );
}
else{
	dbQuery( 'update ratings set '
		. 'rating="' . $rating . '"'
		. ',date="' . $date . '"'
		. ' where name="' . $name . '"'
	);
	die( 'update' );
}
// }

?>
