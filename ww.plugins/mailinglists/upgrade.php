<?php
/**
	* upgrade script for the mailinglists plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version==0) {
	dbQuery(
		'create table mailinglists_lists('
		.'id int auto_increment not null primary key,'
		.'name text,'
		.'meta text'
		.')default charset=utf8'
	);
	dbQuery(
		'create table mailinglists_people('
		.'id int auto_increment not null primary key,'
		.'name text,'
		.'email text,'
		.'meta text'
		.')default charset=utf8'
	);
	$version=1;
}
if ($version==1) {
	dbQuery(
		'create table mailinglists_lists_people('
		.'people_id int default 0,'
		.'lists_id int default 0,'
		.'meta text'
		.')default charset=utf8'
	);
	$version=2;
}
if ($version==2) {
	dbQuery(
		'create table mailinglists_issues_automated('
		.'id int auto_increment not null primary key'
		.', list_id int'
		.', period text'
		.', next_issue_date date'
		.', template text'
		.', active smallint default 0'
		.', mode smallint default 0'
		.') default charset=utf8'
	);
	$version=3;
}
if ($version==3) {
	dbQuery('alter table mailinglists_issues_automated drop mode');
	dbQuery(
		'alter table mailinglists_issues_automated change period period smallint'
	);
	dbQuery(
		'alter table mailinglists_issues_automated change template template int'
	);
	dbQuery(
		'create table mailinglists_templates('
		.'id int auto_increment not null primary key'
		.', name text, meta text)default charset=utf8'
	);
	$version=4;
}
