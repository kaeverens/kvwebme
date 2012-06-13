<?php
/**
  * upgrade script
  *
  * PHP Version 5
  *
	* @category   Whatever
  * @package    Webme
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) { // issuetracker_types
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `issuetracker_types` (
			`id` int(11) NOT NULL auto_increment,
			`name` text,
			`fields` text,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // issuetracker_projects
	dbQuery(
		'create table issuetracker_projects('
		.'id int not null primary key auto_increment, name text,'
		.'parent int default 0,meta text)default charset=utf8'
	);
	$version=2;
}
if ($version==2) {
	dbQuery(
		'alter table issuetracker_projects change parent parent_id int default 0'
	);
	$version=3;
}
if ($version==3) {
	dbQuery(
		'create table issuetracker_issues('
		.'id int auto_increment not null primary key, date_created datetime,'
		.'date_modified datetime, name text, status smallint default 1,'
		.'project_id int default 0, type_id int default 0)default charset=utf8'
	);
	$version=4;
}
if ($version==4) {
	dbQuery('alter table issuetracker_issues add meta text');
	$version=5;
}
