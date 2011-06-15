<?php
/**
 * upgrade.php, kvWebME A/B Testing plugin
 *
 * @author  Kae Verens <kae@kvsites.ie>
 * @license GPL 2.0
 * @version 1.0
 */

if( $version == 0 ){
	dbQuery( 'create table abtesting_pages ('
		.'from_id int default 0,'
		.'variant_chosen int default 0,'
		.'ipaddress text'
		.')default charset=utf8'
	);
	$version = 1;
}

$DBVARS[ $pname . '|version' ] = $version;
config_rewrite( );
