<?php

/**
 * info.php, KV-Webme Ratings Plugin
 *
 * echos info about a certain product
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../ww.incs/basics.php';

// { validate input
$name = addslashes( @$_POST[ 'name' ] );
if( $name == '' )
	exit;
// }

$votes = dbAll( 'select * from ratings where name="' . $name . '"' );
$votes = count( $votes );

echo $votes . ' people have rated this.';
exit;

?>
