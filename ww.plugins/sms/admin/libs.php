<?php
if (!Core_isAdmin()) {
	Core_quit();
}

function SMS_callApi($command, $urifrag='') {
	global $DBVARS;
	$url='http://textr.mobi/api.php'
		.'?a='.urlencode($command)
		.'&email='.urlencode($DBVARS['sms_email'])
		.'&password='.urlencode($DBVARS['sms_password'])
		.str_replace(' ', '+', $urifrag);
	$f=file_get_contents($url);
	if ($f===false) {
		return false;
	}
	return json_decode($f);
}
function SMS_getCreditBalance() {
	$f=SMS_callApi('check-credits');
	return (int)$f->credits;
}
function SMS_getCreditPrice() {
	$f=SMS_callApi('check-credits-price');
	return (float)$f->price;
}
