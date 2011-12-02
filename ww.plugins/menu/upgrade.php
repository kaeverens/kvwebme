<?php
if ($version=='0') { // add menu table
	dbQuery(
		'create table if not exists menus(id int auto_increment not null '
		.'primary key,parent int default 0,direction smallint default 1)'
		.'default charset=utf8'
	);
	$version=1;
}
if ($version=='1') { // add background colours
	dbQuery('alter table menus add background char(7) default "#ff0000"');
	$version=2;
}
if ($version=='2') { // add opacity
	dbQuery('alter table menus add opacity float default .95');
	$version=3;
}
if ($version=='3') { // columns
	dbQuery('alter table menus add columns smallint default 1');
	$version=4;
}
if ($version=='4') { // menu type (0=drop-down, 1=accordion)
	dbQuery('alter table menus add type tinyint default 0');
	$version=5;
}
if ($version=='5') { // where to inherit styles from
	dbQuery('alter table menus add style_from tinyint default 0');
	// set existing ones back to 1
	dbQuery('update menus set style_from=1');
	$version=6;
}
if ($version=='6') {
	// create state column in database
	dbQuery('alter table menus add state text');
	// set default value for state
	dbQuery('update menus set state=0');
	$version=7;
}
