<?php
/**
	* Upgrade file for the SMS plugin
	* 
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

if ($version==0) { // online_store_orders
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `sms_addressbooks` (
		`id` int(11) NOT NULL auto_increment,
		`name` text,
		`subscribers` text,
		`date_created` datetime,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `sms_subscribers` (
		`id` int(11) NOT NULL auto_increment,
		`name` text,
		`phone` text,
		`date_created` datetime,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
