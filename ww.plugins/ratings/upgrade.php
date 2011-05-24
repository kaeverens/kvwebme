<?php

/**
 * upgrade.php, KV-Webme Ratings Plugin
 *
 * upgrades the themes api to the latest version
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

if( $version == 0 ){

	dbQuery( 'create table ratings (
			id int auto_increment primary key,
			name text,
			rating int,
			type text,
			date text,
			user int
			)
	' );

	$version = 1;

}

$DBVARS[ $pname . '|version' ] = $version;
config_rewrite( );
