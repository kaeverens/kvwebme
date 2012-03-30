<?php
if ($version == 0) { // protect_files table
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `protected_files` (
		`id` int(11) NOT NULL auto_increment,
		`directory` text,
		`recipient_email` text,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version == 1) { // template to use, and record accesses
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `protected_files_log` (
		`ip` char(15),
		`file` text,
		`last_access` datetime,
		`success` smallint default 0,
		`email` text
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	dbQuery('alter table protected_files add template text');
	$version=2;
}
if ($version == 2) { // use sessioned MD5 as key, not IP
	dbQuery('alter table protected_files_log add session_md5 char(32)');
	dbQuery('alter table protected_files_log add key(session_md5)');
	$version=3;
}
if ($version == 3) { // add message to protected_files;
	dbQuery('alter table protected_files add message text');
	$version=4;
}
if ($version == 4) { // add link back to main protected_files entry
	dbQuery('alter table protected_files_log add pf_id int default 0');
	$version=5;
}
if ($version == 5) { // add protected-by-group
	dbQuery('alter table protected_files add details text');
	dbQuery('update protected_files set details=\'{"type":1}\'');
	$version=6;
}
