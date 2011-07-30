<?php
/**
	* kvWebME A/B Testing plugin upgrade script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version == 0) {
	dbQuery(
		'create table abtesting_pages ('
		.'from_id int default 0,'
		.'variant_chosen int default 0,'
		.'ipaddress text'
		.')default charset=utf8'
	);
	$version = 1;
}
