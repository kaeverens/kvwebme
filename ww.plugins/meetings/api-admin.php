<?php

function Meetings_adminCustomerCreate() {
	$name=$_REQUEST['name'];
	dbQuery('insert into user_accounts set name="'.addslashes($name).'"');
	$id=dbLastInsertId();
	$gid=(int)dbOne('select id from groups where name="customers"', 'id');
	if (!$gid) {
		dbOne('insert into groups set name="customers"');
		$gid=dbLastInsertId();
	}
	dbQuery(
		'insert into users_groups set user_accounts_id='.$id.', groups_id='.$gid
	);
	return array(
		'id'=>$id
	);
}
function Meetings_adminCustomersList() {
	return dbAll(
		'select user_accounts.id as id,user_accounts.name as name'
		.' from user_accounts,users_groups,groups'
		.' where user_accounts_id=user_accounts.id and groups_id=groups.id'
		.' and groups.name in ("customers")'
	);
}
function Meetings_adminEmployeeCreate() {
	$name=$_REQUEST['name'];
	dbQuery('insert into user_accounts set name="'.addslashes($name).'"');
	$id=dbLastInsertId();
	$gid=(int)dbOne('select id from groups where name="employees"', 'id');
	if (!$gid) {
		dbOne('insert into groups set name="employees"');
		$gid=dbLastInsertId();
	}
	dbQuery(
		'insert into users_groups set user_accounts_id='.$id.', groups_id='.$gid
	);
	return array(
		'id'=>$id
	);
}
function Meetings_adminEmployeesList() {
	return dbAll(
		'select user_accounts.id as id,user_accounts.name as name'
		.' from user_accounts,users_groups,groups'
		.' where user_accounts_id=user_accounts.id and groups_id=groups.id'
		.' and groups.name in ("employees", "administrators")'
	);
}
function Meetings_adminFormEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$fields=$_REQUEST['fields'];
	$sql=$id?'update':'insert into';
	$sql.=' forms_nonpage set name="'.addslashes($name).'"'
		.',fields="'.addslashes(json_encode($fields)).'"';
	$sql.=$id?' where id='.$id:'';
	dbQuery($sql);
	return array(
		'ok'=>$sql
	);
}
function Meetings_adminFormsList() {
	return dbAll('select * from forms_nonpage order by name');
}
function Meetings_adminMeetingEdit() {
	$id=(int)$_REQUEST['id'];
	$user_id=(int)$_REQUEST['user_id'];
	$customer_id=(int)$_REQUEST['customer_id'];
	$form_id=(int)$_REQUEST['form_id'];
	$meeting_time=$_REQUEST['meeting_time'];
	$sql=$id?'update':'insert into';
	$sql.=' meetings set meeting_time="'.addslashes($meeting_time).'"'
		.',user_id='.$user_id.',customer_id='.$customer_id
		.',form_id='.$form_id;
	$sql.=$id?' where id='.$id:'';
	dbQuery($sql);
	return array(
		'ok'=>1
	);
}
function Meetings_adminMeetingGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from meetings where id='.$id);
}
