<?php
if ($version==0) { // messaging_notifier
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `messaging_notifier` (
		`id` int(11) NOT NULL auto_increment,
		`messages_to_show` int default 10,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // messaging_notifier_rows
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `messaging_notifier_rows` (
		`mn_id` int(11),
		`mn_type` varchar(10),
		`mn_url` text
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=2;
}
if ($version==2) { // remove messaging_notifier_rows
	dbQuery('DROP TABLE messaging_notifier_rows');
	dbQuery('alter table messaging_notifier add data text');
	$version=3;
}
