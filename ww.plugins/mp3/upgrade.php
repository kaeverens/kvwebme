<?php
/**
	* MP3 plugin upgrade
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version<1) {
	dbQuery(
		'create table if not exists mp3_plugin('
		.'id int auto_increment not null primary key,'
		.'fields text,'
		.'template text'
		.')	default charset=utf8;'
	);
	$version=1;
}
