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
	$lists=dbAll('select id,name from mailinglists_lists order by name');
	foreach ($lists as $k=>$v) {
		$lists[$k]['subscribers']=dbOne(
			'select count(people_id) as subscribers'
			.' from mailinglists_lists_people where lists_id='.$v['id'],
			'subscribers'
		);
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
	return array('ok'=>1);
}

// }
