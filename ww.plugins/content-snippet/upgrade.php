<?php
/**
	* upgrade script for content snippets
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version<1) { // add table
	dbQuery(
		'create table if not exists content_snippets('
		.'id int auto_increment not null primary key,'
		.'html text'
		.')	default charset=utf8;'
	);
	$version=1;
}
if ($version=='1') { // convert to accordion
	dbQuery('alter table content_snippets add accordion smallint default 0');
	dbQuery(
		'alter table content_snippets '.
		'add accordion_direction smallint default 0'
	); // 0 is horizontal
	$rs=dbAll('select * from content_snippets');
	if (count($rs) && isset($rs[0]['html'])) {
		foreach ($rs as $r) {
			$arr=array(
				array('title'=>'', 'html'=>$r['html'])
			);
			dbQuery(
				'update content_snippets set html="'
				.addslashes(json_encode($arr))
				.'" where id='.$r['id']
			);
		}
		Core_cacheClear('content_snippets');
	}
	dbQuery('alter table content_snippets change html content text');
	$version=2;
}
if ($version=='2') { // add directory of images
	dbQuery('alter table content_snippets add images_directory text');
	$version=3;
}
