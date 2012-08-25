<?php
/**
  * Blog upgrade file
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if ($version==0) {
	dbQuery(
		'create table blog_entry('
		.'id int auto_increment not null primary key,'
		.'title text,'
		.'excerpt text,'
		.'excerpt_image text,'
		.'body text,'
		.'tags text,'
		.'user_id int default 0,'
		.'cdate datetime'
		.')default charset=utf8'
	);
	$version=1;
}
if ($version==1) {
	dbQuery('alter table blog_entry add udate datetime');
	$version=2;
}
if ($version==2) {
	dbQuery('alter table blog_entry add pdate datetime');
	$version=3;
}
if ($version<5) {
	dbQuery('alter table blog_entry add comments int default 0');
	$version=5;
}
if ($version==5) {
	dbQuery('alter table blog_entry add allow_comments smallint default 1');
	$version=6;
}
if ($version==6) {
	dbQuery('alter table blog_entry add status smallint default 1');
	$version=7;
}
if ($version==7) {
	dbQuery(
		'CREATE TABLE `blog_comment` ( `id` int(11) NOT NULL AUTO_INCREMENT,'
		.' `user_id` int(11) DEFAULT 0, `name` text, `url` text, `email` text,'
		.' `comment` text, `cdate` datetime DEFAULT NULL,'
		.' `blog_entry_id` int(11) DEFAULT 0, `status` smallint(6) DEFAULT 0,'
		.' `verification` varchar(32) DEFAULT NULL, PRIMARY KEY (`id`)'
		.' ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
	);
	$version=8;
}
if ($version==8) {
	dbQuery(
		'CREATE TABLE `blog_tags` ('
		.'`entry_id` int(11) DEFAULT 0, `tag` text)'
		.'ENGINE=InnoDB DEFAULT CHARSET=utf8'
	);
	$version=9;
}
if ($version==9) {
	dbQuery('alter table blog_entry drop published');
	$version=10;
}
if ($version==10) { // featured stories
	dbQuery('alter table blog_entry add featured tinyint default 0;');
	$version=11;
}
if ($version==11) { // fix intermittent ID problem
	dbQuery(
		'alter table blog_entry change id id int auto_increment not null'
		.' primary key'
	);
	$version=12;
}
