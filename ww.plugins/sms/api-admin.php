<?php
require_once SCRIPTBASE.'ww.plugins/sms/admin/libs.php';
function Sms_adminAddressbookDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from sms_addressbooks where id='.$id);
	return ('err'=>0, 'id'=>$id);
}
function Sms_adminAddressbooksSave() {
	$name=$_REQUEST['name'];
	$id=(int)$_REQUEST['id'];
	$subscribers=$_REQUEST['subscribers'];
	if (!$name
		|| $name=='[insert addressbook name]'
		|| preg_replace('/[0-9,]*/', '', $subscribers)!=''
	) {
		return array('err'=>1);
	}
	if ($id<1) {
		dbQuery(
			'insert into sms_addressbooks (name,subscribers,date_created) '
			.'values("'.addslashes($name).'","['.$subscribers.']",now())'
		);
	}
	else {
		dbQuery(
			'update sms_addressbooks set name="'.addslashes($name)
			.'",subscribers="['.$subscribers.']" where id='.$id
		);
	}
	return array('err'=>0);
}
function Sms_adminAddressbooksGet() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select id,name,subscribers from sms_addressbooks where id='.$id);
	$r['subscribers']=json_decode($r['subscribers']);
	return $r;
}
function Sms_adminAddressbooksSubscribersGet() {
	$id=(int)$_REQUEST['id'];
	$r=dbRow('select subscribers from sms_addressbooks where id='.$id);
	$subs=explode(',',str_replace(array('[',']','"'),'',$r['subscribers']));
	$rs=dbAll('select id,name,phone from sms_subscribers order by name');
	foreach($rs as $k=>$r){
		if(in_array($r['id'],$subs))$rs[$k]['c']=1;
	}
	return $rs;
}
function Sms_adminButtonPaypalGet() {
	$amt=(int)$_REQUEST['amt'];
	if ($amt<200) {
		exit;
	}
	$return=urlencode(
		'http://'.$_SERVER['HTTP_HOST']
		.'/ww.admin/plugin.php?_plugin=sms&_page=dashboard'
	);
	$ret=SMS_callApi('order-credits', '&credits='.$amt.'&return='.$return);
	return $ret;
}
function Sms_adminSend() {
	// { to
	$to=$_REQUEST['to'];
	if (!$to || preg_replace('/[^0-9]/','',$to)!=$to) {
		exit;
	}
	// }
	// { msg
	$msg=$_REQUEST['msg'];
	if (!$msg || preg_replace('/a-zA-Z0-9 !_\-.,:\'"/', '', $msg)!=$msg) {
		exit;
	}
	// }
	// { to_name
	$to_name=$_REQUEST['to_name'];
	if (!$to_name) {
		$to_name=$to;
	}
	// }
	$ret=SMS_callApi(
		'send',
		'&to='.$to.'&message='.urlencode($msg).'&name='.urlencode($to_name)
	);
	return $ret;
}
function Sms_adminSendBulk() {
	$aid=(int)$_REQUEST['to'];
	$msg=$_REQUEST['msg'];
	if (!$msg || preg_replace('/a-zA-Z0-9 !_\-.,:\'"/', '', $msg)!=$msg) {
		exit;
	}
	$tos=array();
	$to_names=array();
	$subs=dbOne(
		'select subscribers from sms_addressbooks where id='.$aid.' limit 1',
		'subscribers'
	);
	$subs=dbAll(
		'select name,phone from sms_subscribers where id in ('
		.preg_replace('/[^0-9,]/', '', $subs).')'
	);
	foreach ($subs as $sub) {
		$tos[]=$sub['phone'];
		$to_names[]=preg_replace('/[^a-zA-Z0-9 \-.\']/', '', $sub['name']);
	}
	$ret=SMS_callApi(
		'send-bulk',
		'&to='.join(',', $tos).'&message='.urlencode($msg).'&names='
		.join(',', $to_names)
	);
	return $ret;
}
function Sms_adminSubscribe() {
	if(!isset($_REQUEST['email'])
		|| !filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)
		|| !isset($_REQUEST['pass'])
		|| !$_REQUEST['pass']
	){
		return array(
			'status'=>0,
			'error'=>'email and password must be provided'
		);
	}
	$url='http://textr.mobi/api.php?a=subscribe'
		.'&email='.urlencode($_REQUEST['email'])
		.'&password='.urlencode($_REQUEST['pass']);
	$res=file_get_contents($url);
	if($res===false){
		return array(
			'status'=>0,
			'error'=>'failed to contact textr.mobi. please wait a short while '
			.'and try again.'
		);
	}
	$json=json_decode($res);
	if($json->status){ // successful subscription. record details
		$DBVARS['sms_email']=$_REQUEST['email'];
		$DBVARS['sms_password']=$_REQUEST['pass'];
		Core_configRewrite();
	}
	return $json;
}
function Sms_adminSubscribersDelete() {
	$id=(int)$_REQUEST['id'];
	$addressBooks = dbAll('select id, subscribers from sms_addressbooks');
	foreach ($addressBooks as $book) {
		$subs = json_decode($book['subscribers']);
		if (!in_array($id, $subs)) {
			continue;
		}
		for ($i=0; $i<count($subs); ++$i) {
			if ($subs[$i]==$id) {
				unset($subs[$i]);
				break;
			}
		}
		$subs = json_encode($subs);
		dbQuery(
			'update sms_addressbooks set subscribers = "'.$subs.'" where id = '
			.$book['id']
		);
	}
	dbQuery('delete from sms_subscribers where id='.$id);
	echo '{"err":0,"id":'.$id.'}';
}
function Sms_adminSubscribersGet() {
	return isset($_REQUEST['id'])
		?dbRow('select * from sms_subscribers where id='.$_REQUEST['id'])
		:dbAll('select id,name from sms_subscribers order by name');
}
function Sms_adminSubscribersSave() {
	$name=$_REQUEST['name'];
	$id=(int)$_REQUEST['id'];
	$phone=$_REQUEST['phone'];
	if(!$name || $name=='[insert subscriber name]'){
		return array('err'=>1);
	}
	if(preg_replace('/[^0-9]/','',$phone)!=$phone
		|| !preg_match('/^44|^353/',$phone)
	) {
		return array('err'=>2);
	}
	if ($id<1) {
		dbQuery(
			'insert into sms_subscribers (name,phone,date_created) values("'
			.addslashes($name).'","'.$phone.'",now())'
		);
	}
	else {
		dbQuery(
			'update sms_subscribers set name="'.addslashes($name).'",phone="'
			.$phone.'" where id='.$id
		);
	}
	return array('err'=>0);
}
function Sms_adminActivate() {
	$url='http://textr.mobi/api.php?a=activate'
		.'&email='.urlencode($DBVARS['sms_email'])
		.'&activation='.urlencode($_REQUEST['key']);
	$res=file_get_contents($url);
	if($res===false){
		return array(
			'status'=>0,
			'error'=>'failed to contact textr.mobi. please wait a short while '
			.'and try again.'
		);
	}
	return json_decode($res);
}
