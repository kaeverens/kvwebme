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
		.'cdate datetime,'
		.'published smallint default 0'
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
