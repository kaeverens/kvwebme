<?php
/**
	* upgrade script for ads
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
		'create table ads (id int auto_increment not null primary key,'
		.'name text, customer_id int default 0, views int default 0,'
		.'clicks int default 0, image_url text, target_url text, cdate date,'
		.'is_active smallint default 0)default charset=utf8'
	);
	$version=1;
}
if ($version==1) {
	dbQuery(
		'create table ads_types(id int not null auto_increment primary key,'
		.'name text, width int default 0, height int default 0)'
		.'default charset=utf8;'
	);
	dbQuery('alter table ads add type_id int default 0');
	$version=2;
}
if ($version==2) {
	dbQuery('alter table ads_types add price_per_day float default 0');
	dbQuery('alter table ads add date_expire date');
	dbQuery('update ads set date_expire=date_add(now(), interval 1 year)');
	$version=3;
}
if ($version==3) {
	dbQuery(
		'create table ads_purchase_orders('
		.'id int auto_increment not null primary key,user_id int,type_id int,'
		.'days int,target_url text)default charset=utf8'
	);
	$version=4;
}
if ($version==4) {
	dbQuery(
		'create table ads_track(ad_id int default 0, click int default 0,'
		.'view int default 0, cdate datetime);'
	);
	$version=5;
}
if ($version==5) {
	dbQuery('alter table ads add target_type smallint default 0');
	$version=6;
}
if ($version==6) {
	dbQuery('alter table ads_purchase_orders add target_type smallint default 0');
	$version=7;
}
if ($version==7) {
	dbQuery('alter table ads_purchase_orders add poster text');
	$version=8;
}
if ($version==8) {
	dbQuery('alter table ads add poster text');
	$version=9;
}
