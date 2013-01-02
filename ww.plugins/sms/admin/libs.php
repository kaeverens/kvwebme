<?php
/**
	* SMS thing
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/
if (!Core_isAdmin()) {
	Core_quit();
}

// { SMS_callApi

/**
	* SMS_callApi
	*
	* @param string $command command
	* @param string $urifrag URI fragment
	*
	* @return json
	*/
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

// }
// { SMS_getCreditBalance

/**
	* SMS_getCreditBalance
	*
	* @return credits
	*/
function SMS_getCreditBalance() {
	$f=SMS_callApi('check-credits');
	return (int)$f->credits;
}

// }
// { SMS_getCreditPrice

/**
	* SMS_getCreditPrice
	*
	* @return float
	*/
function SMS_getCreditPrice() {
	$f=SMS_callApi('check-credits-price');
	return (float)$f->price;
}

// }
