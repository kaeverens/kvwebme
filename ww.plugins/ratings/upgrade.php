<?php
/**
	* upgrades the themes api to the latest version
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version == 0) {

	dbQuery(
		'create table ratings (
			id int auto_increment primary key,
			name text,
			rating int,
			type text,
			date text,
			user int
			)'
	);

	$version = 1;

}
if ($version == 1) {

	dbQuery('alter table ratings change user user text');

	$version = 2;
}
