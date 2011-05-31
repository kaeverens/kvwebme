<?php
/*
	Webme Dynamic Search Plugin v0.2
	File: upgrade.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

if($version==0){
	dbQuery('create table latest_search (id int primary key auto_increment,search text,category text,time text,date text)');
	$version=1;
}

if($version==1){
	$version=2;
}

if($version==2){
	dbQuery('insert into site_vars values ("cat","")');
	$version=3;
}

$DBVARS[$pname.'|version']=$version;
config_rewrite();
?>
