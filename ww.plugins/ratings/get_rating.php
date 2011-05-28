<?php

/**
 * get_rating.php, KV-Webme Ratings Plugin
 *
 * returns the rating of the item
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../ww.incs/basics.php';

// { get and validate get data
$name = addslashes( @$_GET[ 'name' ] );
if( $name == '' )
	exit;
// }

$ratings = dbAll( 'select * from ratings where name="' . $name . '"' );
if( count( $ratings ) == 0 )
	die( 'none' );


// removed temporarily
/*
// { exclude and delete ratings over a year old
$valid = array( );
$lastyear = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) -1 );
for( $i = 0; $i < count( $ratings ); ++$i ){
	$date = explode( '-', $ratings[ $i ][ 'date' ] ); 
	$date = mktime( 0, 0, 0, $date[ 1 ], $date[ 0 ], $date[ 2 ] );
	if( $date > $lastyear )
		array_push( $valid, $ratings[ $i ] );
	else
		dbQuery( 'delete from ratings where id=' . $ratings[ $i ][ 'id' ] );
}
$ratings = $valid;
// }
*/


// { calculate rating
$rating = 0;
for( $i = 0; $i < count( $ratings ); ++$i )
	$rating += $ratings[ $i ][ 'rating' ];
$rating = $rating / count( $ratings );
// }

echo $rating;
exit;
?>
