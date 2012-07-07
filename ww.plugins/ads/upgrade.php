<?php
if ($version==0) {
	dbQuery(
		'create table ads (id int auto_increment not null primary key, name text, customer_id int default 0, views int default 0, clicks int default 0, image_url text, target_url text, cdate date, is_active smallint default 0)default charset=utf8'
	);
	$version=1;
}
if ($version==1) {
	dbQuery('create table ads_types(id int not null auto_increment primary key, name text, width int default 0, height int default 0)default charset=utf8;');
	dbQuery('alter table ads add type_id int default 0');
	$version=2;
}
