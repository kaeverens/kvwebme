<?php
/**
  * admin api
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

// { IssueTracker_adminProjectSave

/**
	* save details about a project, or create a project
	*
	* @return array saved project
	*/
function IssueTracker_adminProjectSave() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$meta=json_encode($_REQUEST['meta']);
	$parent_id=(int)$_REQUEST['parent_id'];
	$sql='set name="'.addslashes($name).'"'
		.', meta="'.addslashes($meta).'", parent_id='.$parent_id;
	// { groups
	$groups='|';
	if (isset($_REQUEST['meta']['groups'])) {
		foreach ($_REQUEST['meta']['groups'] as $v) {
			$groups.=$v.'|';
		}
	}
	$sql.=',groups="'.addslashes($groups).'"';
	// }
	// { users
	$users='|';
	if (isset($_REQUEST['meta']['users'])) {
		foreach ($_REQUEST['meta']['users'] as $v) {
			$users.=$v.'|';
		}
	}
	$sql.=',users="'.addslashes($users).'"';
	// }
	if ($id) {
		dbQuery('update issuetracker_projects '.$sql.' where id='.$id);
		return array('id'=>$id);
	}
	else {
		dbQuery('insert into issuetracker_projects '.$sql);
		return array('id'=>dbLastInsertId());
	}
}

// }
// { IssueTracker_adminTypeGet

/**
	* get an issue type's details
	*
	* @return array list
	*/
function IssueTracker_adminTypeGet() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select * from issuetracker_types where id='.$id);
	$r['fields']=json_decode($r['fields']);
	return $r;
}

// }
// { IssueTracker_adminTypeNew

/**
	* get a list of issue types
	*
	* @return array list
	*/
function IssueTracker_adminTypeNew() {
	$name=$_REQUEST['name'];
	if (!$name) {
		return array('error'=>'no name provided');
	}
	$sql='select id from issuetracker_types where name="'.addslashes($name).'"';
	if (dbOne($sql, 'id')) {
		return array('error'=>'an issue type with that name already exists');
	}
	dbQuery(
		'insert into issuetracker_types set name="'.addslashes($name).'"'
		.', fields="[]"'
	);
	return array('id'=>dbLastInsertId());
}

// }
// { IssueTracker_adminTypeSet

/**
	* set an issue type's details
	*
	* @return array list
	*/
function IssueTracker_adminTypeSet() {
	$id=(int)$_REQUEST['id'];
	$fields=json_encode($_REQUEST['fields']);
	$sql='update issuetracker_types set fields="'.addslashes($fields).'" where id='.$id;
	dbQuery($sql);
}

// }
