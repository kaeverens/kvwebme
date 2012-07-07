<?php

function Meetings_meetingsGet() {
	return dbAll(
		'select * from meetings where user_id='.$_SESSION['userdata']['id']
		.' and !is_complete'
	);
}
function Meetings_customersGet() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	return dbAll(
		'select user_accounts.id as id,user_accounts.name as name'
		.' from user_accounts,users_groups,groups'
		.' where user_accounts_id=user_accounts.id and groups_id=groups.id'
		.' and groups.name in ("customers")'
	);
}
function Meetings_formsGet() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	return dbAll(
		'select * from forms_nonpage'
	);
}
function Meetings_formSubmit() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	$id=(int)$_REQUEST['id'];
	$values=$_REQUEST['values'];
	mail('kae.verens@gmail.com', 'test', print_r($_REQUEST, true));
	dbQuery(
		'update meetings set form_values="'
		.addslashes($values).'" where id='.$id
	);
}
function Meetings_complete() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	$id=(int)$_REQUEST['id'];
	dbQuery('update meetings set is_complete=1 where id='.$id);
}
