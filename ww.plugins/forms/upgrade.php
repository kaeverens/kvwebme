<?php
/**
  * upgrade script for Form plugin
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

if ($version==0) { // forms_fields
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forms_fields` (
			`id` int(11) NOT NULL auto_increment,
			`name` text,
			`type` text,
			`isrequired` smallint(6) default 0,
			`formsId` int(11) default NULL,
			`extra` text,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // forms_saved
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forms_saved` (
			`forms_id` int(11) default 0,
			`date_created` datetime default NULL,
			`id` int(11) NOT NULL auto_increment,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=2;
}
if ($version==2) { // forms_saved_values
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forms_saved_values` (
			`forms_saved_id` int(11) default 0,
			`name` text,
			`value` text,
			`id` int(11) NOT NULL auto_increment,
			PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=3;
}
if ($version==3) { // replace FIELD{blah} with blah
	$r=dbAll('show tables like "page_vars"', 'table');
	if (count($r)) {
		$rs=dbAll('select * from page_vars where name="forms_replyto"');
		foreach ($rs as $r) {
			dbQuery(
				'update page_vars set value="'
				.preg_replace('/^FIELD{|}$/', '', $r['value'])
				.'" where page_id='.$r['page_id'].' and name="forms_replyto"'
			);
		}
	}
	$version=4;
}
if ($version==4) { // copy forms fields to page_vars
	$rs=dbAll('select distinct formsId from forms_fields');
	foreach ($rs as $r) {
		$json='select name, type, isrequired, extra from forms_fields'
			.' where formsId='.$r['formsId'].' order by id';
		dbQuery(
			'insert into page_vars set name="forms_fields", page_id='.$r['formsId']
			.', value="'.addslashes(json_encode(dbAll($json))).'"'
		);
	}
	Core_cacheClear();
	$version=5;
}
// note: remove forms_fields table after 2012-08-22
if ($version<7) { // create forms_nonpage
	dbQuery(
		'create table forms_nonpage('
		.'id int auto_increment not null primary key,'
		.'name text,'
		.'fields text'
		.')default charset utf8'
	);
	$version=7;
}
if ($version<8) { // add template to forms_nonpage
	dbQuery('alter table forms_nonpage add template text');
	$version=8;
}
