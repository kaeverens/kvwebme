<?php
/**
	* api
	*
	* PHP Version 5
	*
	* @category   None
	* @package    None
	* @subpackage None
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
		return array('error'=>__('you are not logged in'));
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
		.', due_date=date_add("'.addslashes($dueDate).'", interval '
		.$recurring_multiplier
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
		
	$extras=json_decode($extras, true);
	$votesLeft = $extras['free-credits'];
	
	if (!array_key_exists('free-credits', $extras)) {
		//the user hasn't been initialised
		$extras['free-credits']=(int)dbOne(
			'select * from site_vars where name="max-free-credits"',
			'value'
		);
		$votesLeft = $extras['free-credits'];
	}

	if ($votesLeft>=1) {
		//substract 1 from free-credits
			
		$votesLeft -=1;
		$paidCreditsLeft = json_decode($extras)->{'paid_credits'};
	
		//votesToId represents how many votes the user gave to the id issue
			
		$votesToId = $extras['votesTo-'.$id];
	
		if (($votesToId)==null) { //this is the first time the user votes
			$votesToId = 1;
		}
		else { //increase it with 1
			$votesToId=(int)$votesToId+1;
		}
		$extras['free-credits'] = $votesLeft;
		$extras['paid_credits'] = $paidCreditsLeft==null?0:$paidCreditsLeft;
		$extras['votesTo-'.$id] = $votesToId;
					
		dbQuery(
			'update user_accounts set extras="'.addslashes(json_encode($extras)).'"'
			.' where id='.$_SESSION['userdata']['id']
		);
	
		//add a vote to the project
					
		$meta = dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
		$meta = json_decode($meta, true);
		$meta["credits"] += 1;
		if (array_key_exists("votesFrom-".$_SESSION['userdata']['id'], $meta)) {
			$meta["votesFrom-".$_SESSION['userdata']['id']] += 1;
		}
		else {
			$meta["votesFrom-".$_SESSION['userdata']['id']] = 1;
		}
	
		dbQuery(
			'update issuetracker_issues set meta="'.addslashes(json_encode($meta))
			.'" where id='.$id
		);
		return $meta['credits'];
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
	$jExtras = json_decode($extras);
	$votesLeft = (int)$jExtras->{'free-credits'};
	$votesToId = (int)$jExtras->{'votesTo-'.$id};
	
	if ($votesToId==null || $votesToId==0) { //the user didn't vote here
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
			'update user_accounts set extras="'.addslashes(json_encode($extras)).'"'
			.' where id='.$_SESSION['userdata']['id']
		);
		// }
		// { substract a vote from the project
		$meta = dbOne('select meta from issuetracker_issues where id='.$id, 'meta');
		$jMeta = json_decode($meta, true);
		$jMeta['credits'] = $jMeta['credits']-1;
		$jMeta['votesFrom-'.$_SESSION['userdata']['id']] -= 1;
		if ($jMeta['votesFrom-'.$_SESSION['userdata']['id']] == 0) {
			unset($jMeta['votesFrom-'.$_SESSION['userdata']['id']]);
			dbQuery(
				'update issuetracker_issues set meta="'.addslashes(json_encode($jMeta))
				.'" where id='.$id
			);
		}
		// }
		return $jMeta['credits'];
	}
}

// }
// { IssueTracker_payPalHttpPost

/**
	* Send HTTP POST Request
	*
	* @param string $methodName_ The API method name
	* @param string $nvpStr_     The POST Message fields in &name=value pair form
	* @param string $environment The environment i.e live or sandbox
	*
	* @return	array	Parsed HTTP Response body
	*/
function IssueTracker_payPalHttpPost($methodName_, $nvpStr_, $environment) {
	global $environment;

	// Set up your API credentials, PayPal end point, and API version.
	$API_UserName = urlencode('k_ounu_1352645404_biz_api1.yahoo.com');
	$API_Password = urlencode('1352645423');
	$API_Signature = urlencode(
		'AWTh1QaXzqTsshBiWRQudg8pQwR8AI2SjDZAq60ymfHzw2yy4zfCkXI4 '
	);
	$API_Endpoint = "https://api-3t.paypal.com/nvp";
	if ("sandbox" === $environment || "beta-sandbox" === $environment) {
		$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
	}
	$version = urlencode('51.0');

	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt(
		$ch,
		CURLOPT_HTTPHEADER,
		array('Content-type: application/x-www-form-urlencoded;charset=UTF-8')
	);
	curl_setopt($ch, CURLOPT_URL, $API_Endpoint);		
	curl_setopt($ch, CURLOPT_VERBOSE, 1);

	// Turn off the server and peer verification (TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);

	// Set the API operation, version, and API signature in the request.
	$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password"
		."&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

	// Set the request as a POST FIELD for curl.
	curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	// Get response from the server.
	$httpResponse = curl_exec($ch);

	if (!$httpResponse) {
		return "$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')';
	}

	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if (sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}

	if ((0 == sizeof($httpParsedResponseAr))
		|| !array_key_exists('ACK', $httpParsedResponseAr)
	) {
		return "Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.";
	}

	return $httpParsedResponseAr;
}

// }
// { IssueTracker_setPaypalAddress()

/**
	* sets the email where the money will be deposited
	*
	* @return null
	*/
function IssueTracker_setPaypalAddress() {
	$email=$_REQUEST['email'];
	$email_site_vars=dbOne(
		"select * from `site_vars` where `name`='paypal_address'", 'value'
	);
	if ($email_site_vars) {
		dbQuery(
			'update site_vars set `value`="'.$_REQUEST['email'].'"'
			.' where `name`="paypal_address"'
		);
	}
	else {
		dbQuery(
			'INSERT INTO `site_vars` (`name`,`value`)'
			.' VALUES("paypal_address","'.$_REQUEST['email'].'")'
		);
	}
}

// }
// { IssueTracker_getPaypalAddress()

/**
	* returns the email where the money will be deposited
	*
	* @return string
	*/
function IssueTracker_getPaypalAddress() {
	$email=dbOne(
		"select * from site_vars where `name`='paypal_address'", 'value'
	);
	return $email;
}


// }
// { IssueTracker_payMoney()
/**
	* pays money to address
	*
	* @return null
	*/
function IssueTracker_payMoney() {
	$environment = 'sandbox';	// or 'beta-sandbox' or 'live'  

	// Set request-specific fields.
	
	$paypal_address=dbOne(
		'select * from `site_vars` where `name`="paypal_address"', 'value'
	);
	$emailSubject = urlencode($paypal_address);
	$receiverType = urlencode('EmailAddress');
	$currency = urlencode('EUR');
	// or other currency ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')

	// Add request-specific fields to the request string.
	$nvpStr='&EMAILSUBJECT='.$emailSubject.'&RECEIVERTYPE='.$receiverType
		.'&CURRENCYCODE='.$currency.'&METHOD=MassPay';

	$receiversArray = array();
	$receiverData = array(
		'receiverEmail' => $_REQUEST['email'],
		'amount' => $_REQUEST['amount'],
		'note' => "This is a payment for completing issue number "
			.$_REQUEST['issue_number']
	);
	
	$receiverEmail = urlencode($receiverData['receiverEmail']);
	$amount = urlencode($receiverData['amount']);
	$note = urlencode($receiverData['note']);
	$nvpStr .= "&L_EMAIL$i=$receiverEmail&L_Amt$i=$amount&L_UNIQUEID$i=$uniqueID'
		.'&L_NOTE$i=$note";

	// Execute the API operation;
	$httpParsedResponseAr = IssueTracker_payPalHttpPost('MassPay', $nvpStr);
	if (!is_array($httpParsedResponseAr)) {
		return $httpParsedResponseAr;
	}

	if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"])
		|| "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])
	) {
		return 'MassPay Completed Successfully: '
			.print_r($httpParsedResponseAr, true);
	}
	else  {
		return 'MassPay failed: ' . print_r($httpParsedResponseAr, true);
	}
}

// }
// { IssueTracker_finishIssue()

/**
	* marks the Issue as Complete and returns the votes to users
	*
	* @return null
	*/
function IssueTracker_finishIssue() {
	$id = (int)$_REQUEST['id'];
	$meta = dbOne('select * from issuetracker_issues where id='.$id, 'meta');

	$meta = json_decode($meta, true);
	
	//we return the votes to each user
	foreach ($meta as $k=>$v) {
		if (strpos($k, 'votesFrom-') !== false) {
			$pieces = explode("-", $k);
			$userId = $pieces[1];

			if ($userId) {
				$extras=dbOne(
					"select * from `user_accounts` where `id`=".$userId,
					'extras'
				);
				$extras =json_decode($extras, true);
				unset($extras['votesTo-'.$id]);
				$extras['free-credits'] += $v;
				print_r($extras);
				dbQuery(
					'update `user_accounts` set `extras`="'
					.addslashes(json_encode($extras)).'"'
					.'" where `id`='.$userId
				);
			}
		}
	}

	//mark the issue as complete
	dbQuery("update `issuetracker_issues` set `status`=2 where id=".$id);
		
	//unset the extras field
	dbQuery(
		'update issuetracker_issues set meta="'.addslashes(json_encode(array())).'"'
		.' where `id`='.$id
	);
}

// }
// { IssueTracker_getDepositedValue()

/**
	* returns the amount of money that a issue has
	*
	* @return null
	*/
function IssueTracker_depositValue() {
	$id = $_REQUEST['id'];
	$amount = $_REQUEST['amount'];
	$from = $_SESSION['userdata']['id'];
	if (!$from) {
		return 'Please log in';
	}

	$extras = dbOne(
		"select `extras` from `user_accounts` where `id`=".$from, 'extras'
	);
	$extras = json_decode($extras, true);

	if ((int)$extras['paid_credits'] < (int)$amount) {
		return 'You do not have enough credits for this operation';
	}

	if ($amount < 0) {
		return 'Please insert a valid number';
	}

	if (!is_numeric($amount)) {
		return 'Please insert a number';
	}

	$extras['paid_credits'] -= $amount;
	dbQuery(
		"update `user_accounts` set `extras`='".json_encode($extras)
		."' where `id`=".$from
	);

	$meta = dbOne(
		"select `meta` from `issuetracker_issues` where `id`=".$id, 'meta'
	);
	$meta = json_decode($meta, true);

	$status = dbOne(
		"select `status` from `issuetracker_issues` where `id`=".$id, 'status'
	);
	if ($status == 2) {
		return "This issue has 'complete' status"; 
	} 
	
	if (array_key_exists('paid_credits', $meta)) {
		$meta['paid_credits'] += $amount;
	}
	else {
		$meta['paid_credits'] = $amount;
	}
	
	dbQuery(
		'update issuetracker_issues set `meta`="'.addslashes(json_encode($meta)).'"'
		.' where `id`='.$id
	);
	return "OK";
}

// }
// { IssueTracker_getDepositedValue()

/**
	* returns the amount of money that a issue has
	*
	* @return null
	*/
function IssueTracker_getDepositedValue() {
	$id = $_REQUEST['id'];
	$amount = 0;
	$meta = dbOne(
		"select `meta` from `issuetracker_issues` where `id`=".$id, 'meta'
	);
	$meta = json_decode($meta, true);
	if (array_key_exists('paid_credits', $meta)) {
		$amount = $meta['paid_credits'];	
	}
	return $amount;
}

// }
