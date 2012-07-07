<?php
if ($version==0) {
	dbQuery(
		'CREATE TABLE `meetings` ('
		.'`id` int(11) NOT NULL auto_increment,'
		.'`form_id` int(11) default 0,'
		.'`user_id` int(11) default 0,'
		.'`customer_id` int(11) default 0,'
		.'`location` text,'
		.'`meeting_time` datetime default NULL,'
		.'`form_values` text,'
		.'PRIMARY KEY  (`id`)'
		.') ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8'
	);
	dbQuery('alter table meetings add is_complete smallint default 0');
	$version=1;
}
