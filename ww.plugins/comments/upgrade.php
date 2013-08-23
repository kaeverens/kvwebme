<?php

/**
  * Database managment for comments plugin
  *
  * PHP Version 5
  *
  * @category   CommentsPlugin
  * @package    WebworksWebme
  * @subpackage CommentsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
**/

if ($version<=1) {
	dbQuery(
		'create table if not exists comments(
			id int not null auto_increment primary key,
			objectid int default 0,
			name text,
			email text,
			homepage text,
			comment text,
			cdate datetime,
			isvalid smallint,
			verificationhash char(28)
		)Engine MyISAM default charset="utf8"'
	);
	$version = 2;
}
if ($version==2) {
	dbQuery('alter table comments drop verificationhash');
	$version = 3;
}
