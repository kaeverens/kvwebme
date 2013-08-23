<?php
if ($version<4) { // panels table
	dbQuery('CREATE TABLE IF NOT EXISTS `panels` (
		`id` int(11) NOT NULL auto_increment,
		`name` text,
		`body` text,
		`visibility` text,
		`disabled` smallint default 0,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
	$version=4;
}
if ($version==4) { // pages that this panel is hidden on.
	dbQuery('alter table panels add hidden text');
	$version=5;
}
