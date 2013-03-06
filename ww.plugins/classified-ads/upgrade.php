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
if ($version==4) {
	dbQuery(
		'alter table classifiedads_ad add phone text'
	);
	dbQuery(
		'alter table classifiedads_ad add title text'
	);
	dbQuery(
		'alter table classifiedads_ad add location text'
	);
	dbQuery(
		'alter table classifiedads_ad add excerpt text'
	);
	$version=5;
}
if ($version==5) {
	dbQuery(
		'alter table classifiedads_types add minimum_number_of_days int default 0'
	);
	dbQuery(
		'alter table classifiedads_types add number_of_images int default 0'
	);
	$version=6;
}
if ($version==6) {
	dbQuery(
		'create table classifiedads_purchase_orders('
		.'id int auto_increment not null primary key,user_id int,type_id int,'
		.'days int,title text, description text, category_id int'
		.')default charset=utf8'
	);
	$version=7;
}
if ($version==7) {
	dbQuery('alter table classifiedads_purchase_orders add phone varchar(255)');
	dbQuery(
		'alter table classifiedads_purchase_orders add location varchar(255)'
	);
	dbQuery('alter table classifiedads_purchase_orders add cost varchar(255)');
	$version=8;
}
