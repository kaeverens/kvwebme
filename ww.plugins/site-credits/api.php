<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!isset($PLUGINS['site-credits'])) {
	echo '{"error":"the site-credits plugin is not installed"}';
	exit;
}
if (!isset($DBVARS['sitecredits-apikey'])) {
	echo '{"error":"the site-credits does not have an API key set"}';
	exit;
}
if (!isset($_REQUEST['time'])) {
	echo '{"error":"you must supply a \'time\' parameter"}';
	exit;
}
if ($_REQUEST['time']<time()-3600) {
	echo '{"error":"\'time\' parameter too old"}';
	exit;
}

function SiteCredits_apiVerify($vars, $sha1) {
	ksort($vars);
	$vars['time']=(int)$vars['time'];
	$json=json_encode($vars);
	return sha1($json.'|'.$GLOBALS['DBVARS']['sitecredits-apikey']) == $sha1;
}

switch ($_REQUEST['action']) {
	case 'add-credits': // {
		if (SiteCredits_apiVerify(array(
			'action'=>'add-credits',
			'credits'=>(float)$_REQUEST['credits'],
			'time'=>$_REQUEST['time']),
			$_REQUEST['sha1']
		)) {
			$credits=(float)@$GLOBALS['DBVARS']['sitecredits-credits'];
			$GLOBALS['DBVARS']['sitecredits-credits']=
				$credits + (float)$_REQUEST['credits'];
			config_rewrite();
			echo '{"credits":'.(float)$GLOBALS['DBVARS']['sitecredits-credits'].'}';
			exit;
		}
		break; // }
	case 'check-credits': // {
		if (SiteCredits_apiVerify(array(
			'action'=>'check-credits',
			'time'=>$_REQUEST['time']),
			$_REQUEST['sha1']
		)) {
			echo '{"credits":'.(float)@$GLOBALS['DBVARS']['sitecredits-credits'].'}';
			exit;
		}
		break; // }
	case 'set-option': // {
		dbQuery(
			'delete from sitecredits_options where name="'
			.addslashes($_REQUEST['payment-recipient']).'"'
		);
		dbQuery(
			'insert into sitecredits_options set name="'
			.addslashes($_REQUEST['name']).'", value="'
			.addslashes($_REQUEST['value']).'"'
		);
		echo '{"credits":'.(float)@$GLOBALS['DBVARS']['sitecredits-credits'].'}';
		exit; // }
	default: // {
		echo '{"error":"unknown action"}';
		exit;
	// }
}

echo '{"error":"checksum failed"}';
