<?php
/**
  * upgrade script for Form plugin
  *
  * PHP Version 5
  *
	* @category   Whatever
  * @package    WebworksWebme
  * @subpackage Form
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
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
	$r=dbAll('show tables like "page_vars"','table');
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

$DBVARS[$pname.'|version']=$version;
config_rewrite();
