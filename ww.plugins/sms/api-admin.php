<?php
function Sms_adminAddressbookSave() {
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
function Sms_adminSubscribersGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from sms_subscribers where id='.$id);
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
