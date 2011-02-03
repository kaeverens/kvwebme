<?php
if(!isset($GLOBALS['kfm_parameters']['version_db']))$GLOBALS['kfm_parameters']['version_db']=0;
$dbv=$GLOBALS['kfm_parameters']['version_db'];
if($dbv==0){
	$GLOBALS['kfmdb']->query("insert into ".KFM_DB_PREFIX."parameters (name,value) values ('version_db','1')");
	$dbv=1;
}
if($dbv==1){
	switch($GLOBALS['kfm_db_type']){
		case 'mysql': // {
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."translations(original TEXT INDEXED,translation TEXT,language VARCHAR(2),calls INT DEFAULT 0,found INT DEFAULT 1)DEFAULT CHARSET=utf8");
		break;
		// }
		case 'pgsql': case 'sqlite': case 'sqlitepdo': // {
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."translations(original TEXT,translation TEXT,language VARCHAR(2),calls INTEGER DEFAULT 0,found INTEGER DEFAULT 0)");
		// }
	}
	$dbv=2;
}
if($dbv==2){
	if($GLOBALS['kfm_db_type']=='pgsql'){
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."users( id serial, username varchar(16), password varchar(40), status INTEGER default 2)");
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."settings( id serial, name varchar(128), value varchar(256), user_id INTEGER, usersetting INTEGER default 0)");
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."plugin_extensions( id serial, extension varchar(64), plugin varchar(64), user_id INTEGER)");
	}
	$dbv=3;
}
if($dbv==3){
	if($GLOBALS['kfm_db_type']=='mysql'){
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."translations(original TEXT,translation TEXT,language VARCHAR(2),calls INT DEFAULT 0,found INT DEFAULT 1)DEFAULT CHARSET=utf8");
	}
	$dbv=4;
}
if($dbv==4){
	$GLOBALS['kfmdb']->query("ALTER TABLE ".KFM_DB_PREFIX."translations ADD context text");
	$dbv=5;
}
if($dbv==5){
	$GLOBALS['kfmdb']->query("DELETE FROM ".KFM_DB_PREFIX."translations");
	$dbv=6;
}
if($dbv==6){
	$GLOBALS['kfmdb']->query("ALTER TABLE ".KFM_DB_PREFIX."directories ADD maxwidth INT DEFAULT 0");
	$GLOBALS['kfmdb']->query("ALTER TABLE ".KFM_DB_PREFIX."directories ADD maxheight INT DEFAULT 0");
	$dbv=7;
}
if($dbv==7){
	switch($GLOBALS['kfm_db_type']){
		case 'mysql': // {
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."settings ( `id` int(11) NOT NULL auto_increment, `name` varchar(128) default NULL, `value` varchar(256) default NULL, `user_id` int(8) default NULL, `usersetting` int(1) default '0', PRIMARY KEY  (`id`))DEFAULT CHARSET=utf8");
		break;
		// }
		case 'pgsql': case 'sqlite': case 'sqlitepdo': // {
		$GLOBALS['kfmdb']->query("CREATE TABLE ".KFM_DB_PREFIX."settings ( `id` INTEGER NOT NULL auto_increment, `name` varchar(128) default NULL, `value` varchar(256) default NULL, `user_id` INTEGER default NULL, `usersetting` INTEGER default '0', PRIMARY KEY  (`id`))");
		// }
	}
	$dbv=8;
}

$GLOBALS['kfmdb']->query("update ".KFM_DB_PREFIX."parameters set value='$dbv' where name='version_db'");
echo '<p>Database updated. Please reload page.</p><script>document.location="./";</script>';
exit;
