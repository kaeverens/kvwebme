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

// { Issuetracker_commentAdd

/**
	* add a comment to an issue
	*
	* @return array status
	*/
function Issuetracker_commentAdd() {
	$iid=(int)$_REQUEST['issue_id'];
	$body=$_REQUEST['body'];
	$uid=@$_SESSION['userdata']['id'];
	if (!$uid) {
		return array(
			'error'=>__('you are not logged in')
		);
	}
	dbQuery(
		'insert into issuetracker_comments'
		.' set user_id='.$uid.', body="'.addslashes($body).'"'
		.', cdate=now(), issue_id='.$iid
	);
	return array(
		'ok'=>1
	);
}

// }
// { Issuetracker_commentsGet

/**
	* get comments attached to an issue
	*
	* @return array status
	*/
function Issuetracker_commentsGet() {
	$iid=(int)$_REQUEST['id'];
	$rs=dbAll(
		'select * from issuetracker_comments where issue_id='.$iid
		.' order by cdate'
	);
	foreach ($rs as $k=>$r) {
		$rs[$k]['name']=dbOne(
			'select name from user_accounts where id='.$r['user_id'], 'name'
		);
	}
	return $rs;
}

// }
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
	$dueDate=$_REQUEST['dueDate'];
	$newMeta=$_REQUEST['meta'];
	$meta=dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
	$meta=json_decode($meta, true);
	foreach ($newMeta as $k=>$v) {
		$meta[$k]=$v;
	}
	$sql='update issuetracker_issues set date_modified=now()'
		.', name="'.addslashes($name).'", status='.$status
		.', due_date="'.addslashes($dueDate).'"'
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
			$orderby='due_date';
		break;
		case 2:
			$orderby='status';
		break;
		case 3:
			$orderby='name';
		break;
		default:
			$orderby='due_date';
	}
	$filters=array('issuetracker_issues.project_id=issuetracker_projects.id');
	if ($search) {
		$filters[]='name like "%'.addslashes($search).'%"';
	}
	if ($pid) {
		$filters[]='project_id='.$pid;
	}
	$filter='';
	if (!Core_isAdmin()) { // check projects for restrictions
		$projects=dbAll(
			'select id, name, groups, users from issuetracker_projects', 'id'
		);
		$allowed_projects=array(0);
		foreach ($projects as $p) {
			if (strlen($p['groups'])>1) {
				if (!isset($_SESSION['userdata'])) {
					continue;
				}
				$ok=0;
				foreach ($_SESSION['userdata']['groups'] as $k=>$v) {
					if (strpos($p['groups'], '|'.$v.'|')!==false) {
						$ok=1;
					}
				}
				if (!$ok) {
					continue;
				}
			}
			if (strlen($p['users'])>1) {
				if (!isset($_SESSION['userdata'])) {
					continue;
				}
				if (strpos($p['users'], '|'.$_SESSION['userdata'].'|')===false) {
					continue;
				}
			}
			$allowed_projects[]=$p['id'];
		}
		$filters[]='issuetracker_projects.id in ('.join(',', $allowed_projects).')';
	}
	if (count($filters)) {
		$filter='where ('.join(') and (', $filters).')';
	}
	$sql='select issuetracker_issues.id id'
		.', type_id, issuetracker_issues.name name, status, project_id'
		.', issuetracker_projects.name project_name'
		.', due_date'
		.' from issuetracker_issues,issuetracker_projects '.$filter
		.' order by '.$orderby.' '.$orderdesc
		.' limit '.$start.','.$length;
	$rs=dbAll($sql);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from issuetracker_issues', 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(issuetracker_issues.id) as ids from issuetracker_issues, issuetracker_projects '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array();
		// { id
		$row[]=$r['id'];
		// }
		$row[]=$r['due_date'];
		// { status
		$row[]=(int)$r['status'];
		// }
		// { name
		$row[]=__FromJson($r['name']);
		// }
		// { type
		$row[]=(int)$r['type_id'];
		// }
		// { project
		$row[]=$r['project_name'];
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
	$hotels=array();
	$rs=dbAll(
		'select id,name,parent_id,meta from issuetracker_projects where parent_id=0'
	);
	foreach ($rs as $r) {
		if (Core_isAdmin()) {
			$hotels[]=$r;
			continue;
		};
		$p=json_decode($r['meta'], true);
		if (count($p['groups'])) {
			$ok=0;
			foreach ($p['groups'] as $v) {
				if (in_array($v, $_SESSION['userdata']['groups'])) {
					$ok=1;
				}
			}
			if (!$ok) {
				continue;
			}
			$hotels[]=$r;
			continue;
		}
		if (count($p['users'])) {
			$ok=0;
			if (in_array($_SESSION['userdata']['id'], $p['users'])) {
				$ok=1;
			}
			if (!$ok) {
				continue;
			}
			$hotels[]=$r;
			continue;
		}
		$hotels[]=$r;
	}
	return $hotels;
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
