<?php
/**
  * api
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

// { Issuetracker_issueCreate

/**
	* create an issue
	*
	* @return array status
	*/
function Issuetracker_issueCreate() {
	$name=$_REQUEST['name'];
	$type_id=(int)$_REQUEST['type_id'];
	$project_id=(int)$_REQUEST['project_id'];
	$sql='insert into issuetracker_issues'
		.' set name="'.addslashes($name).'"'
		.', type_id='.$type_id
		.', project_id='.$project_id
		.', meta="{}"'
		.', date_created=now()'
		.', date_modified=now()'
		.', status=1';
	dbQuery($sql);
	$id=dbLastInsertId();
	return array('id'=>$id);
}

// }
// { Issuetracker_issueGet

/**
	* get issue
	*
	* @return array
	*/
function Issuetracker_issueGet() {
	$id=(int)$_REQUEST['id'];
	$issue=dbRow('select * from issuetracker_issues where id='.$id);
	$files=array();
	if (file_exists(USERBASE.'/f/issue-tracker-files/'.$id)) {
		$dir=new DirectoryIterator(USERBASE.'/f/issue-tracker-files/'.$id);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$files[]=$file->getFilename();
		}
	}
	return array(
		'issue'=>$issue,
		'files'=>$files,
		'type'=>dbRow(
			'select * from issuetracker_types where id='.$issue['type_id']
		)
	);
}

// }
// { Issuetracker_issueSet

/**
	* set issue
	*
	* @return array
	*/
function Issuetracker_issueSet() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$status=(int)$_REQUEST['status'];
	$newMeta=$_REQUEST['meta'];
	$meta=dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
	$meta=json_decode($meta, true);
	foreach ($newMeta as $k=>$v) {
		$meta[$k]=$v;
	}
	$sql='update issuetracker_issues set date_modified=now()'
		.', name="'.addslashes($name).'", status='.$status
		.', meta="'.addslashes(json_encode($meta)).'"'
		.' where id='.$id;
	dbQuery($sql);
	return array('ok'=>1);
}

// }
// { Issuetracker_issuesGetDT

/**
	* get issue overviews
	*
	* @return array
	*/
function Issuetracker_issuesGetDT() {
	$pid=(int)$_REQUEST['pid'];
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$search=$_REQUEST['sSearch'];
	$orderby=(int)$_REQUEST['iSortCol_0'];
	$orderdesc=$_REQUEST['sSortDir_0']=='desc'?'desc':'asc';
	switch ($orderby) {
		case 1:
			$orderby='name';
		break;
		case 2:
			$orderby='status';
		break;
		default:
			$orderby='name';
	}
	$filters=array();
	if ($search) {
		$filters[]='name like "%'.addslashes($search).'%"';
	}
	if ($pid) {
		$filters[]='project_id='.$pid;
	}
	$filter='';
	if (count($filters)) {
		$filter='where ('.join(') and (', $filters).')';
	}
	$sql='select id, type_id, name, status'
		.' from issuetracker_issues '.$filter
		.' order by '.$orderby.' '.$orderdesc
		.' limit '.$start.','.$length;
	$rs=dbAll($sql);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from issuetracker_issues', 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(id) as ids from issuetracker_issues '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array();
		// { name
		$row[]=__FromJson($r['name']);
		// }
		// { type
		$row[]=(int)$r['type_id'];
		// }
		// { status
		$row[]=(int)$r['status'];
		// }
		// { id
		$row[]=$r['id'];
		// }
		$arr[]=$row;
	}
	$result['aaData']=$arr;
	return $result;
}

// }
// { IssueTracker_typesGet

/**
	* get a list of issue types
	*
	* @return array list
	*/
function IssueTracker_typesGet() {
	return dbAll('select id,name from issuetracker_types order by name');
}

// }
// { Issuetracker_projectGet

/**
	* get project
	*
	* @return array
	*/
function Issuetracker_projectGet() {
	$id=(int)$_REQUEST['id'];
	$project=dbRow('select * from issuetracker_projects where id='.$id);
	return $project;
}

// }
// { IssueTracker projectsGet

/**
	* get a list of projects
	*
	* @return array list
	*/
function IssueTracker_projectsGet() {
	return dbAll(
		'select id,name,parent_id,meta from issuetracker_projects where parent_id=0'
	);
}

// }
// { IssueTracker_issueFileUpload

/**
	* upload a file for an issue
	*
	* @return status
	*/
function IssueTracker_issueFileUpload() {
	$id=(int)$_REQUEST['id'];
	$fname=USERBASE.'/f/issue-tracker-files/'.$id.'/'.$_FILES['Filedata']['name'];
	if (strpos($fname, '..')!==false) {
		return array('message'=>'invalid file url');
	}
	@mkdir(dirname($fname), 0777, true);
	$from=$_FILES['Filedata']['tmp_name'];
	move_uploaded_file($from, $fname);
	return array('ok'=>1);
}

// }
