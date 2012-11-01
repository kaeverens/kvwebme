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
	$recurring_multiplier=(int)$_REQUEST['recurring_multiplier'];
	$recurring_type=$_REQUEST['recurring_type'];
	$recurring_types=array('day', 'week', 'month', 'year');
	if (!in_array($recurring_type, $recurring_types)) {
		$recurring_type='day';
	}
	$oldData=dbRow('select * from issuetracker_issues where id='.$id);
	$meta=json_decode($oldData['meta'], true);
	foreach ($newMeta as $k=>$v) {
		$meta[$k]=$v;
	}
	$sql='update issuetracker_issues set date_modified=now()'
		.', name="'.addslashes($name).'", status='.$status
		.', due_date="'.addslashes($dueDate).'"'
		.', recurring_type="'.$recurring_type.'"'
		.', recurring_multiplier='.$recurring_multiplier
		.', meta="'.addslashes(json_encode($meta)).'"'
		.' where id='.$id;
	dbQuery($sql);
	if ($recurring_multiplier>0 && $oldData['status']=='1' && $status==2) {
		$sql='insert into issuetracker_issues set date_modified=now()'
			.', name="'.addslashes($name).'", status=1'
			.', due_date=date_add("'.addslashes($dueDate).'", interval '.$recurring_multiplier
			.' '.$recurring_type.')'
			.', recurring_type="'.$recurring_type.'"'
			.', recurring_multiplier='.$recurring_multiplier
			.', meta="'.addslashes(json_encode($meta)).'"'
			.', type_id='.$oldData['type_id']
			.', project_id='.$oldData['project_id']
			.', date_created=now()';
		dbQuery($sql);
	}
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
	$filters=array(
		'issuetracker_issues.project_id=issuetracker_projects.id',
		'((due_date>="'.addslashes($_REQUEST['date-from']).'"'
		.' and due_date<"'.addslashes($_REQUEST['date-to']).' 24")'
		.' or due_date="0000-00-00")'
	);
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
		.', issuetracker_issues.meta meta, due_date'
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
		'select count(issuetracker_issues.id) as ids'
		.' from issuetracker_issues, issuetracker_projects '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array();
		$rMeta=json_decode($r['meta']);
		// { id
		$row[]=$r['id'];
		// }
		// { due_date
		$row[]=$r['due_date'];
		// }
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
		// { credits
		
		$freeCredits=$rMeta->{'credits'};
		$paidCredits=$rMeta->{'paid_credits'};
		$metaArray=array();
		$metaFlag=false;
		
		if ($freeCredits==null) { // initialise the meta for the first time
			$metaArray['credits']=0;
			$metaFlag=true;
		}
		else {
			$metaArray['credits']=$rMeta->{'credits'};
		}

		if ($paidCredits==null) { //same thing here
			$metaArray['paid_credits']=0;
			$metaFlag=true;
		}
		else {
			$metaArray['paid_credits']=$rMeta->{'paid_credits'};		
		}

		if ($metaFlag) {
			$sql='update issuetracker_issues set meta="'
				.json_encode($metaArray).'" where id='.$r['id'];	
			dbQuery($sql);
		}

		$row[]=($rMeta->{'credits'}!=null?$rMeta->{'credits'}:0);
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
// { IssueTracker_addVote

/**
	* add a vote
	*
	* @return null
	*/
function IssueTracker_addVote() {
	$id=(int)$_REQUEST['id'];
	if (!$_SESSION['userdata']['id']) {
		return "You must log in first";
	}
	
	$extras = dbOne(
		'select extras from user_accounts where id='.$_SESSION['userdata']['id'],
		'extras'
	);
	$votesLeft  = (int)json_decode($extras)->{'free-credits'};
	
	if ($extras=='[]') {
		$extras=array();
		//this user hasn't been initialised
		$extras['free-credits']=(int)dbOne(
			'select * from site_vars where name="max-free-credits"',
			'value'
		);
		$votesLeft=$extras['free-credits'];
		$extras['paid-credits']=0;
	}
	
	if ($votesLeft>=1) {
		//substract 1 from free-credits
		
		$votesLeft -=1;
		$paidCreditsLeft = json_decode($extras)->{'paid_credits'};

		//votesToId represents how many votes the user gave to the id issue
		
		$votesToId = json_decode($extras)->{'votesTo-'.$id};

		if (($votesToId)==null) { //this is the first time the user votes
			$votesToId = 1;
		}
		else { //increase it with 1
			$votesToId=$votesToId+1;
		}

		$extras=json_decode($extras, true);
		
		$extras['free-credits']=$votesLeft;
		$extras['paid_credits']=$paidCreditsLeft==null?0:$paidCreditsLeft;
		$extras['votesTo-'.$id]=$votesToId;
		dbQuery(
			'update user_accounts set extras="'.json_encode($extras)
			.'" where id='.$_SESSION['userdata']['id']
		);
		
		//add a vote to the project
		$meta=dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
		
		$votes = (int)json_decode($meta)->{"credits"};
		$votes+=1;

		$paidCredits = json_decode($meta)->{"paid_credits"};
		
		$meta=array("credits"=>$votes, "paid_credits"=>$paidCredits);
		dbQuery(
			'UPDATE issuetracker_issues set meta="'.addslashes(json_encode($meta))
			.'" WHERE id='.$id
		);
		return $votes;
	}
	else {
		return "You don't have enough free credits";
	}
}

// }
// { IssueTracker_substractVote

/**
	* subtract a vote
	*
	* @return null
	*/
function IssueTracker_substractVote() {
	$id=(int)$_REQUEST['id'];
	if (!$_SESSION['userdata']['id']) {
		return "You must log in first";
	}
	
	$extras=dbOne(
		'select extras from user_accounts where id='.$_SESSION['userdata']['id'],
		'extras'
	);
	if ($extras==null) { //this user hasn't been initialised
		$extras['free-credits']=dbOne(
			'select * from site_vars where name="max-free-credits"',
			'value'
		);
		$extras['paid-credits']=0;
	}
	$jExtras=json_decode($extras);
	$votesLeft  = (int)$jExtras->{'free-credits'};
	$votesToId = (int)$jExtras->{'votesTo-'.$id};
	
	if ($votesToId==null || $votesToId==0) {
			//the user didn't vote here
			return "You can't do that";
	}
	else {
		// { add 1 to free-credits
		$votesLeft+=1;
		$paidCreditsLeft=$jExtras->{'paid_credits'};
		$extras=json_decode($extras, true);
		$extras['free-credits']=$votesLeft;
		$extras['paid_credits']=$paidCreditsLeft==null?0:$paidCreditsLeft;
		$extras['votesTo-'.$id]= $votesToId-1;

		dbQuery(
			"update user_accounts set extras='".json_encode($extras)."' where id="
			.$_SESSION['userdata']['id']
		);
		// }
		// { substract a vote from the project
		$meta=dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
		$jMeta=json_decode($meta, true);
		$votes=(int)$jMeta['credits'];
		$votes-=1;
		$paidCredits=$jMeta['paid_credits'];
		$jMeta['credits']=$votes;
		$jMeta['paid_credits']=$paidCredits;  
		dbQuery(
			'UPDATE issuetracker_issues set meta="'.addslashes(json_encode($jMeta))
			.'" WHERE id='.$id
		);
		// }
		return $votes;
	}
}

// }
