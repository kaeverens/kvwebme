<?php
/**
  * upgrade script for Site Credits plugin
  *
  * PHP Version 5
  *
	* @category   Whatever
  * @package    Webme
  * @subpackage Site Credits
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) { // sitecredits_accounts
	dbQuery(
		'CREATE TABLE IF NOT EXISTS sitecredits_accounts (
			id int(11) NOT NULL auto_increment,
			cdate datetime,
			description text,
			amt float,
			total float,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // sitecredits_recurring
	dbQuery(
		'create table if not exists sitecredits_recurring (
			id int not null auto_increment primary key,
			description text,
			amt float default 0,
			start_date date,
			period text,
			next_payment_date date
		) default charset=utf8'
	);
	$version=2;
}
if ($version==2) { // sitecredits_options
	dbQuery(
		'create table if not exists sitecredits_options (
			name text,
			value text
		) default charset=utf8'
	);
	$version=3;
}
