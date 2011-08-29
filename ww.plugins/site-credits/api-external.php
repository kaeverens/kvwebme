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
		$params=array(
			'action'=>'add-credits',
			'credits'=>(float)$_REQUEST['credits'],
			'time'=>$_REQUEST['time']
		);
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
			$credits=(float)@$GLOBALS['DBVARS']['sitecredits-credits'];
			$GLOBALS['DBVARS']['sitecredits-credits']
				=$credits + (float)$_REQUEST['credits'];
			Core_configRewrite();
			echo '{"credits":'.(float)$GLOBALS['DBVARS']['sitecredits-credits'].'}';
			exit;
		}
	break; // }
	case 'check-credits': // {
		$params=array(
			'action'=>'check-credits',
			'time'=>$_REQUEST['time']
		);
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
			echo '{"credits":'.(float)@$GLOBALS['DBVARS']['sitecredits-credits'].'}';
			exit;
		}
	break; // }
	case 'set-option': // {
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
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
		}
	case 'set-hosting-fee': // {
		$params=array(
			'action'=>'set-hosting-fee',
			'cdate'=>$_REQUEST['cdate'],
			'credits'=>(float)$_REQUEST['credits'],
			'time'=>$_REQUEST['time']
		);
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
			dbQuery('delete from sitecredits_recurring where description="hosting"');
			dbQuery(
				'insert into sitecredits_recurring set description="hosting"'
				.',amt='.((float)$_REQUEST['credits'])
				.',start_date="'.addslashes($_REQUEST['cdate']).'",period="1 month"'
				.',next_payment_date="'.addslashes($_REQUEST['cdate']).'"'
			);
			echo '{"ok":1}';
			exit;
		}
	break; // }
	default: // {
		echo '{"error":"unknown action '.addslashes($_REQUEST['action']).'"}';
		exit;
		// }
}

echo '{"error":"checksum failed"}';
/*
mysql> describe sitecredits_recurring;
+-------------------+---------+------+-----+---------+----------------+
| Field             | Type    | Null | Key | Default | Extra          |
+-------------------+---------+------+-----+---------+----------------+
| id                | int(11) | NO   | PRI | NULL    | auto_increment |
| description       | text    | YES  |     | NULL    |                |
| amt               | float   | YES  |     | 0       |                |
| start_date        | date    | YES  |     | NULL    |                |
| period            | text    | YES  |     | NULL    |                |
| next_payment_date | date    | YES  |     | NULL    |                |
+-------------------+---------+------+-----+---------+----------------+

*/
