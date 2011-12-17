<?php
/**
	* Webme Dynamic Search Plugin upgrade script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor.macaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version==0) {
	dbQuery(
		'create table latest_search (id int primary key auto_increment,'
		.'search text,category text,time text,date text)'
	);
	$version=1;
}
if ($version==1) {
	$version=2;
}
if ($version==2) {
	dbQuery('insert into site_vars values ("cat","")');
	$version=3;
}
