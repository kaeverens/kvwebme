<?php
/**
	* admin api for Meetings plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Meetings_adminCustomerCreate

/**
	* create a customer
	*
	* @return array
	*/
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

// }
// { Meetings_adminCustomersList

/**
	* get a list of all customers
	*
	* @return array
	*/
function Meetings_adminCustomersList() {
	return dbAll(
		'select user_accounts.id as id,user_accounts.name as name'
		.' from user_accounts,users_groups,groups'
		.' where user_accounts_id=user_accounts.id and groups_id=groups.id'
		.' and groups.name in ("customers")'
	);
}

// }
// { Meetings_adminEmployeeCreate

/**
	* create an employee
	*
	* @return array
	*/
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

// }
// { Meetings_adminEmployeesList

/**
	* get a list of employees
	*
	* @return array
	*/
function Meetings_adminEmployeesList() {
	return dbAll(
		'select user_accounts.id as id,user_accounts.name as name'
		.' from user_accounts,users_groups,groups'
		.' where user_accounts_id=user_accounts.id and groups_id=groups.id'
		.' and groups.name in ("employees", "administrators")'
	);
}

// }
// { Meetings_adminMeetingEdit

/**
	* edit the details of a meeting
	*
	* @return array
	*/

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

// }
// { Meetings_adminMeetingGet

/**
	* get details about a specific meeting
	*
	* @return array
	*/
function Meetings_adminMeetingGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from meetings where id='.$id);
}

// }
// { Meetings_adminMeetingsDataGetDT

/**
	* meetings get
	*
	* @return datatables thing
	*/
function Meetings_adminMeetingsDataGetDT() {
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$search=$_REQUEST['sSearch'];
	$form_id=(int)$_REQUEST['form_id'];
	$filters=array('form_id='.$form_id);
	if ($search) {
		$filters[]='form_values like "%'.addslashes($search).'%"';
	}
	$filter='';
	if (count($filters)) {
		$filter='where '.join(' and ', $filters);
	}
	$rs=dbAll(
		'select form_values from meetings '.$filter
		.' limit '.$start.','.$length
	);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from meetings where form_id='.$form_id, 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(id) as ids from meetings '.$filter,
		'ids'
	);
	$arr=array();
	$fields=json_decode(
		dbOne('select fields from forms_nonpage where id='.$form_id, 'fields'),
		true
	);
	foreach ($rs as $r) {
		$data=json_decode($r['form_values'], true);
		$row=array();
		foreach ($fields as $f) {
			$row[]=isset($data[$f['name']])?$data[$f['name']]:'';
		}
		$arr[]=$row;
	}
	$result['aaData']=$arr;
	return $result;
}

// }
