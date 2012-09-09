<?php

/**
  * upgrades the themes api to the latest version
  *
  * PHP Version 5
  *
  * @category None
  * @package  None
  * @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
  * @license  GPL 2.0
  * @link     http://kvsites.ie
  */

if (!defined('SCRIPTBASE')) { // don't access directly
	Core_quit();
}

/**
 * if not previously installed, set up database
 */
if ($version == 0) {

	/**
	 * install themes_api database
	 */
	dbQuery(
		'create table if not exists themes_api ( id int( 11 ) not null '
		.'auto_increment primary key, name text not null, author int( 11 ), '
		.'description text, version int( 11 ) not null, last_updated date, '
		.'author_url text, tags text, moderated text, rating int( 11 ) ) '
		.'default charset=utf8'
	);
	/** 
		* install themes-api-stats database
		*/
	dbQuery(
		'create table if not exists themes_api_stats ( id int( 11 ) not null '
		.'auto_increment primary key, theme_id int( 11 ) not null, ip_address '
		.'text, version int( 11 ), domain_name text ) default charset=utf8'
	);
	/**
	 * create moderator group
	 */
	dbQuery('insert into groups values ( "", "moderators", "", "{}" )');
	/**
	 * make room in the user files dir to
	 * store the themes
	 */
	if (!is_dir(USERBASE.'/f/themes_api')) {
		mkdir(USERBASE.'/f/themes_api');
	}
	if ( !is_dir(USERBASE.'/f/themes_api/themes') ) {
		mkdir(USERBASE.'/f/themes_api/themes');
	}
	$version = 1;
}
if ( $version == 1 ) {
	/**
	 * make dir to extract files for verification
	 */
	if ( !is_dir(USERBASE.'/f/themes_api/extract') ) {
		mkdir(USERBASE.'/f/themes_api/extract');
	}
	$version = 2;
}
if ( $version == 2 ) {
	dbQuery(
		'create table themes_downloads ( id int auto_increment primary key,'
		.'theme text, referrer text, ip text, downloaded date )'
	);
	$version = 3;
}
