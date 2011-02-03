<?php
/*
	Webme Banner Image Plugin
	File: upgrade.php
	Developers: Conor Mac Aoidh <http://macaoidh.name>
	            Kae Verens <http://verens.com/>
	Report Bugs: <conor@macaoidh.name>
	             <kae@verens.com>
*/

if($version==0) $version='0.1';
if($version=='0.1'){ // banners_images and banners_pages
	dbQuery('create table if not exists banners_images( id int auto_increment not null primary key, html text)default charset=utf8;');
	dbQuery('create table if not exists banners_pages( pageid int, bannerid int);');
	if(file_exists(USERBASE.'f/skin_files/banner.png')){
		mkdir(USERBASE.'f/skin_files/banner-images');
		rename(USERBASE.'f/skin_files/banner.png',USERBASE.'f/skin_files/banner-images/1.png');
		dbQuery('insert into banners_images values(1,"")');
	}
	$version=1;
}
if($version=='1'){ // update table to allow choice of image/HTML
	dbQuery('alter table banners_images add type smallint default 0'); // 0 is image, 1 is HTML
	dbQuery('alter table banners_images add pages smallint default 0'); // 0 is all pages, 1 means check the banners_pages table
	$version=2;
}
if($version=='2'){ // convert all image types to HTML, add a Name to each item
	dbQuery('alter table banners_images add name text');
	require_once dirname(__FILE__).'/frontend/banner-image.php';
	$rs=dbAll('select id from banners_images');
	$o=new stdClass();
	foreach($rs as $r){
		$o->id=(int)$r['id'];
		$html=showBanner($o);
		dbQuery('update banners_images set name="banner_'.$r['id'].'",html="'.addslashes($html).'",type=1 where id='.$r['id']);
	}
	dbQuery('alter table banners_images change type type smallint default 1;');
	$version=3;
}
// note to self. drop "type" in a far future version. doing it right now would be dangerous.
$DBVARS[$pname.'|version']=$version;
config_rewrite();
