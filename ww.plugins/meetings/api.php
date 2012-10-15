<?php
/**
	* api code for Meetings plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Meetings_meetingsGet

/**
	* Meetings_meetingsGet
	*
	* @return array
	*/
function Meetings_meetingsGet() {
	return dbAll(
		'select * from meetings where user_id='.$_SESSION['userdata']['id']
		.' and !is_complete'
	);
}

// }
// { Meetings_customersGet

/**
	* get a customer
	*
	* @return array
	*/
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

// }
// { Meetings_formsGet

/**
	* get a form
	*
	* @return array
	*/
function Meetings_formsGet() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	return dbAll(
		'select * from forms_nonpage'
	);
}

// }
// { Meetings_formSubmit

/**
	* submit a form
	*
	* @return array
	*/
function Meetings_formSubmit() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	$id=(int)$_REQUEST['id'];
	$values=$_REQUEST['values'];
	dbQuery(
		'update meetings set form_values="'
		.addslashes($values).'" where id='.$id
	);
}

// }
// { Meetings_complete

/**
	* mark a meeting as complete
	*
	* @return array
	*/
function Meetings_complete() {
	if (!isset($_SESSION['userdata'])) {
		return false;
	}
	$id=(int)$_REQUEST['id'];
	dbQuery('update meetings set is_complete=1 where id='.$id);
}

// }
