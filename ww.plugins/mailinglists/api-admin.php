<?php
/**
	* mailinglists admin functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Mailinglists_adminAutomatedIssueDelete

/**
	* delete an automated issue
	*
	* @return status
	*/
function Mailinglists_adminAutomatedIssueDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from mailinglists_issues_automated where id='.$id);
}

// }
// { Mailinglists_adminAutomatedIssuesEdit

/**
	* edit an automated issue
	*
	* @return status
	*/
function Mailinglists_adminAutomatedIssuesEdit() {
	$id=(int)$_REQUEST['id'];
	if ($id<0) {
		dbQuery(
			'insert into mailinglists_issues_automated set period=5'
			.', next_issue_date=date_add(now(), interval 1 year), active=0, list_id=0'
		);
	}
}

// }
// { Mailinglists_adminGetDashboardInfo

/**
	* get overview of mailinglists
	*
	* @return info
	*/
function Mailinglists_adminGetDashboardInfo() {
	$lists=dbOne('select count(id) as lists from mailinglists_lists', 'lists');
	$people=dbOne('select count(id) as people from mailinglists_people', 'people');
	return array(
		'numlists'=>(int)$lists,
		'numpeople'=>(int)$people
	);
}

// }
// { Mailinglists_adminListDetails

/**
	* get info about a mailing list
	*
	* @return info
	*/
function Mailinglists_adminListDetails() {
	$id=(int)$_REQUEST['id'];
	$row=dbRow('select * from mailinglists_lists where id='.$id);
	if (!$row['meta']) {
		$row['meta']='{}';
	}
	$row['meta']=json_decode($row['meta']);
	return $row;
}

// }
// { Mailinglists_adminListDelete

/**
	* delete a mailing list
	*
	* @return status
	*/
function Mailinglists_adminListDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from mailinglists_lists where id='.$id);
	Core_cacheClear('mailinglists');
	return array('ok'=>1);
}

// }
// { Mailinglists_adminListsGetMailChimp

/**
	* get a list of mailing lists from MailChimp
	*
	* @return list
	*/
function Mailinglists_adminListsGetMailChimp() {
	$apikey=$_REQUEST['other_GET_params'];
	require_once dirname(__FILE__).'/MCAPI.class.php';
	$api=new MCAPI($apikey);
	$data=$api->lists();
	if ($api->errorCode) {
		return array(
			'error'=>$api->errorCode,
			'message'=>$api->errorMessage
		);
	}
	$lists=array();
	foreach ($data['data'] as $list) {
		$lists[$list['id'].'|'.$list['name']]=$list['name'];
	}
	$lists['zzz']='add new...';
	return $lists;
}

// }
// { Mailinglists_adminListsGetUbivox

/**
	* get a list of mailing lists from Ubivox
	*
	* @return list
	*/
function Mailinglists_adminListsGetUbivox() {
	$bits=explode('|', $_REQUEST['other_GET_params']);
	$apiusername=$bits[0];
	$apipassword=$bits[1];
	$response=Mailinglists_xmlrpcClient(
		$apiusername,
		$apipassword,
		xmlrpc_encode_request('ubivox.get_maillists', array())
	);
	$data=xmlrpc_decode(trim($response));
	$lists=array();
	foreach ($data as $list) {
		$lists[$list['id'].'|'.$list['title']]=$list['title'];
	}
	return $lists;
}

// }
// { Mailinglists_adminListsList

/**
	* get a list of mailing lists
	*
	* @return list
	*/
function Mailinglists_adminListsList() {
	$lists=dbAll('select id,name,meta from mailinglists_lists order by name');
	foreach ($lists as $k=>$v) {
		$lists[$k]['subscribers']=dbOne(
			'select count(people_id) as subscribers'
			.' from mailinglists_lists_people where lists_id='.$v['id'],
			'subscribers'
		);
		$lists[$k]['meta']=json_decode($lists[$k]['meta']);
	}
	return $lists;
}

// }
// { Mailinglists_adminListSave

/**
	* save a mailing list
	*
	* @return status
	*/
function Mailinglists_adminListSave() {
	$id=(int)$_REQUEST['vals']['id'];
	$sql='mailinglists_lists set '
		.'name="'.addslashes($_REQUEST['vals']['name']).'",'
		.'meta="'.addslashes(json_encode($_REQUEST['meta'])).'"';
	if ($id) {
		dbQuery('update '.$sql.' where id='.$id);
	}
	else {
		dbQuery('insert into '.$sql);
	}
	Core_cacheClear('mailinglists');
	return array('ok'=>1);
}

// }
// { Mailinglists_adminAutomatedIssuesListDT

/**
	* get a list of automated issues
	*
	* @return array
	*/
function Mailinglists_adminAutomatedIssuesListDT() {
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$orderby=(int)$_REQUEST['iSortCol_0'];
	$orderdesc=$_REQUEST['sSortDir_0']=='desc'?'desc':'asc';
	switch ($orderby) {
		case 1:
			$orderby='period';
		break;
		case 2:
			$orderby='list_name';
		break;
		case 3:
			$orderby='template';
		break;
		case 4:
			$orderby='next_issue_date';
		break;
		case 5:
			$orderby='active';
		break;
		default:
			$orderby='period';
	}
	$sql='select period, mailinglists_lists.name as list_name, template'
		.', next_issue_date, active, mailinglists_issues_automated.id as id'
		.' from mailinglists_issues_automated left join mailinglists_lists'
		.' on mailinglists_issues_automated.list_id=mailinglists_lists.id'
		.' order by '.$orderby.' '.$orderdesc.' limit '.$start.','.$length;
	$rs=dbAll($sql);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from mailinglists_issues_automated', 'ids'
	);
	$result['iTotalDisplayRecords']=$result['iTotalRecords'];
	$arr=array();
	foreach ($rs as $r) {
		$arr[]=array(
			$r['period'],
			$r['list_name'],
			$r['template'],
			$r['next_issue_date'],
			$r['active'],
			$r['id']
		);
	}
	$result['aaData']=$arr;
	return $result;
}

// }
