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
	if (dbOne(
		'select id from issuetracker_types where name="'.addslashes($name).'"',
		'id'
	)) {
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
