<?php
if($version<1){
	dbQuery('create table if not exists mp3_plugin(
			id int auto_increment not null primary key,
			fields text,
			template text
		)	default charset=utf8;');
	$version=1;
}
$DBVARS[$pname.'|version']=$version;
config_rewrite();
