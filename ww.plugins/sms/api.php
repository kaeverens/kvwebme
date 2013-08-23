<?php
function Sms_subscribe() {
	$name=$_REQUEST['name'];
	$phone=$_REQUEST['phone'];
	if (!$name || $name=='[insert subscriber name]') {
		return array(
			'err'=>1,
			'errmsg'=>'no name provided'
		);
	}
	if (preg_replace('/[^0-9]/', '', $phone)!=$phone
		|| !preg_match('/^44|^353/', $phone)
	) {
		return array(
			'err'=>2,
			'errmsg'=>'incorrect number format'
		);
	}
	$sid=SMS_getSubscriberId($phone, $name);
	if (!$sid) {
		return array(
			'err'=>2,
			'errmsg'=>'incorrect number format'
		);
	}
	$ids=explode(',', $_REQUEST['ids']);
	foreach ($ids as $aid) {
		SMS_subscribeToAddressbook($sid, (int)$aid);
	}
	return array('err'=>0);
}
