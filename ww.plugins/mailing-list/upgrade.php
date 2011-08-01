<?php
/**
	* upgrade script for the mailing list plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if ($version==0) {
	dbQuery('create table if not exists mailing_list(id int auto_increment not null primary key,email text,name text,status text,hash text)default charset=utf8');
	dbQuery('create table if not exists mailing_list_options(id int auto_increment not null primary key,name text,value text)default charset=utf8');
	$from='noreply@webme.eu';
	$subject='Mailing List SUbscription';
	$body='Hi, \n
		You or someone using your email address has applied to join our mailing list. \n
		To approve this subscription please click on the link below: \n
		%link% \n
		Thanks, \n
		The Team';
	dbQuery('insert into mailing_list_options values("","from","'.$from.'")');
	dbQuery('insert into mailing_list_options values("","subject","'.$subject.'")');
	dbQuery('insert into mailing_list_options values("","body","'.$body.'")');
	dbQuery('insert into mailing_list_options values("","dis_pend","1")');
	dbQuery('insert into mailing_list_options values("","dis_sub","1")');
	dbQuery('insert into mailing_list_options values("","col_name","0")');
	dbQuery('insert into mailing_list_options values("","use_bcc","1")');
	dbQuery('insert into mailing_list_options values("","email","noreply@webme.eu")');
	$version='0.1';
}
if ($version=='0.1') {
	dbQuery('insert into mailing_list_options values("","use_js","1")');
	dbQuery('insert into mailing_list_options values("","inp_em","your email")');
	dbQuery('insert into mailing_list_options values("","inp_nm","your name")');
	dbQuery('insert into mailing_list_options values("","inp_sub","Subscribe")');
	$version='0.2';
}
if ($version=='0.2') {
	$version=1;
}
if ($version=='1') { // mobile phone
	dbQuery('insert into mailing_list_options values("","inp_mb","mobile phone")');
	$version=2;
}
if ($version=='2') { // mobile phone again... really must rewrite this
	dbQuery('insert into mailing_list_options values("","col_mobile","0")');
	$version=3;
}
if ($version=='3') { // *sigh*
	dbQuery('alter table mailing_list add mobile text');
	$version=4;
}
