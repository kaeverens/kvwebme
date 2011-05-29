<?php

/**
 * get_ratings.php, KV-Webme Ratings Plugin
 *
 * returns an array of ratings for items
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../ww.incs/basics.php';

// { get and validate get data 
$names = @$_GET[ 'names' ];
if( $names == '' )
	exit;
$names = explode( ',', $names );
// }

// { build query and execute
$query = 'select * from ratings where ';
for(  $i = 0; $i < count( $names ); ++$i ){
	$query .= 'name="' . addslashes( $names[ $i ] ) . '"';
	if( isset( $names[ ( $i + 1 ) ] ) )
		$query .= ' or ';
}
$ratings = dbAll( $query );
// }

// { calculate ratings
$scores = array( );
for( $i = 0; $i < count( $names ); ++$i ){
	$count = 0;
	$num = 0;
	foreach( $ratings as $rating ){
		if( $rating[ 'name' ] == $names[ $i ] ){
			$count += $rating[ 'rating' ];
			++$num;
		}
	}
	if( $count == 0 )
		$count = 'none';
	else
		$count = ( $count / $num );
	$scores[ $names[ $i ] ][ 'rating' ] = $count;
	$scores[ $names[ $i ] ][ 'voters' ] = $num;

}
// }

echo json_encode( $scores );
exit;
?>
