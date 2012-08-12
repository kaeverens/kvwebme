<?php
if ($version==0) {
	dbQuery(
		'create table classifiedads_categories( parent smallint default 0,'
		.' id int auto_increment not null primary key, name text, icon text)'
		.' default charset=utf8;'
	);
	$version=1;
}
if ($version==1) {
	dbQuery(
		'create table classifiedads_types( id int primary key auto_increment,'
		.' name text, width smallint default 0, height smallint default 0,'
		.' maxchars int default 0, price_per_day float default 0)'
		.' default charset=utf8;'
	);
	$version=2;
}
if ($version==2) {
	dbQuery(
		'create table classifiedads_ad ( id int auto_increment not null primary key,'
		.' user_id int default 0, email text, body text, expiry_date date,'
		.' status smallint default 0, cost float default 0, category_id int default 0)'
		.' default charset=utf8;'
	);
	dbQuery('alter table classifiedads_types drop width');
	dbQuery('alter table classifiedads_types drop height');
	$version=3;
}
if ($version==3) {
	dbQuery(
		'alter table classifiedads_ad add creation_date datetime'
	);
	$version=4;
}
