<?php
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."directories(
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name text,
		parent integer not null
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."files(
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name text,
		directory integer not null,
		foreign key (directory) references ".KFM_DB_PREFIX."directories(id)
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."files_images(
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		caption text,
		file_id integer not null,
		width integer default 0,
		height integer default 0,
		foreign key (file_id) references ".KFM_DB_PREFIX."files(id)
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."files_images_thumbs(
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		image_id integer not null,
		width integer default 0,
		height integer default 0,
		foreign key (image_id) references ".KFM_DB_PREFIX."files_images(id)
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."parameters(name text, value text)");
	$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."session (
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		cookie varchar(32) default NULL,
		last_accessed datetime default NULL
	)");
	$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."session_vars (
		session_id INTEGER,
		varname text,
		varvalue text,
		FOREIGN KEY (session_id) REFERENCES ".KFM_DB_PREFIX."session (id)
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."tags(
		id INTEGER PRIMARY KEY AUTOINCREMENT,
		name text
	)");
	$GLOBALS['kfmdb']->query("create table ".KFM_DB_PREFIX."tagged_files(
		file_id INTEGER,
		tag_id  INTEGER,
		foreign key(file_id) references ".KFM_DB_PREFIX."files(id),
		foreign key(tag_id) references ".KFM_DB_PREFIX."tags(id)
	)");
$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."users(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	username text,
	password text,
	status INTEGER default 2
)");
 
$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."settings(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name text,
	value text,
	user_id INTEGER not null,
	usersetting INTEGER default 0
)");
$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."plugin_extensions(
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	extension text,
	plugin text,
	user_id INTEGER not null
)");

$GLOBALS['kfmdb']->query('INSERT INTO '.KFM_DB_PREFIX.'users (id, username, password, status) VALUES (1,"admin", "'.sha1('admin').'",1)');

	$GLOBALS['kfmdb']->query("insert into ".KFM_DB_PREFIX."parameters values('version','1.3')");
	$GLOBALS['kfmdb']->query("insert into ".KFM_DB_PREFIX."directories values(1,'root',0)");
	$db_defined=1;
?>
