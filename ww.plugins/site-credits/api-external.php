<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once dirname(__FILE__).'/libs.php';

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
			$add=(float)$_REQUEST['credits'];
			if ($credits+$add<0) {
				echo '{"error":"this will leave the client with less than 0 credits"}';
				exit;
			}
			$GLOBALS['DBVARS']['sitecredits-credits']=$credits+$add;
			Core_configRewrite();
			SiteCredits_recordTransaction('credits added by hosting provider', $add);
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
	case 'check-recurring': // {
		$params=array(
			'action'=>'check-recurring',
			'time'=>$_REQUEST['time']
		);
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
			$r=dbRow(
				'select * from sitecredits_recurring '
				.'where next_payment_date<date_add(now(), interval 1 week) '
				.'limit 1'
			);
			if (!$r) {
				echo '{"found":"0"}';
			}
			else {
				echo '{"found":"1"}';
			}
			exit;
		}
	break; // }
	case 'handle-recurring': // {
		$domain=$_REQUEST['domain'];
		$params=array(
			'action'=>'handle-recurring',
			'domain'=>$domain,
			'time'=>$_REQUEST['time']
		);
		if (SiteCredits_apiVerify($params, $_REQUEST['sha1'])) {
			$admins=dbAll(
				'select name,email from user_accounts,users_groups '
				.'where groups_id=1 and user_accounts_id=id'
			);
			// { handle anything due today
			$rs=dbAll(
				'select *,date_format(next_payment_date, "%b-%d-%Y") as npd '
				.'from sitecredits_recurring '
				.'where next_payment_date<now()'
			);
			if ($rs && count($rs)) {
				$email="Dear %ADMIN%,\n  your website has been charged the following "
					."recurring items:\n\n";
				$total=0;
				for ($i=0; $i<count($rs); ++$i) {
					$email.=' '.($i+1).': '.($rs[$i]['npd']).', '
						.$rs[$i]['description'].', '.$rs[$i]['amt'].' credits, '
						.'recurring every '.$rs[$i]['period']."\n";
					$total+=$rs[$i]['amt'];
					dbQuery(
						'update sitecredits_recurring set next_payment_date='
						.'date_add(next_payment_date, interval '.$rs[$i]['period'].')'
						.' where id='.$rs[$i]['id']
					);
				}
				$cur_total=(float)@$GLOBALS['DBVARS']['sitecredits-credits'];
				$cur_total-=$total;
				$GLOBALS['DBVARS']['sitecredits-credits']=$cur_total;
				SiteCredits_recordTransaction('recurring costs (hosting, etc)', -$total);
				$email.="\n\nYour new total is $cur_total credits.";
				$subject=' credits updated';
				if ($cur_total<0) {
					$email.="\n\nYOUR SITE HAS BEEN DISABLED BECAUSE YOUR CREDITS ARE BELOW 0.\n\nYour credits are below 0. You must bring your credits back to 0 or higher.";
					$subject=' SITE DISABLED.'.$subject;
				}
				$email.="\n\nPlease note that this is an automated email.\n\nThank you\n"
					.$domain.$subject;
				foreach ($admins as $admin) {
					mail(
						$admin['email'],
						'['.$domain.'] credits updated',
						str_replace('%ADMIN%', $admin['name'], $email),
						"Bcc: kae@verens.com\r\nFrom: no-reply@$domain\r\nReply-To: no-reply@$domain"
					);
				}
				$GLOBALS['DBVARS']['sitecredits-credits']=$cur_total;
				Core_configRewrite();
			}
			// }
			// { handle anything that's left
			$rs=dbAll(
				'select *,date_format(next_payment_date, "%b-%d-%Y") as npd '
				.'from sitecredits_recurring '
				.'where next_payment_date<date_add(now(), interval 1 week) '
				.'order by next_payment_date'
			);
			if (!count($rs)) {
				exit('{"ok":1}');
			}
			$email="Dear %ADMIN%,\n  your website is due payment for the following "
				."recurring items within one week:\n\n";
			$total=0;
			for ($i=0; $i<count($rs); ++$i) {
				$email.=' '.($i+1).': '.($rs[$i]['npd']).', '
					.$rs[$i]['description'].', '.$rs[$i]['amt'].' credits, '
					.'recurring every '.$rs[$i]['period']."\n";
				$total+=$rs[$i]['amt'];
			}
			$cur_total=(float)@$GLOBALS['DBVARS']['sitecredits-credits'];
			if ($total<$cur_total) {
				echo '{"ok":1}';
			}
			$email.="\n\nYour current balance is $cur_total credits, which is not "
				."enough to cover the $total credits which your site will be charged. "
				."Please log into your administration area and increase "
				."your credits balance."
				."\n\nPlease note that this is an automated email.\n\nThank you\n"
				.$domain.' hosting provider';
			foreach ($admins as $admin) {
				mail(
					$admin['email'],
					'['.$domain.'] credits reminder',
					str_replace('%ADMIN%', $admin['name'], $email),
					"BCC: kae@verens.com\nFrom: no-reply@$domain\nReply-To: no-reply@$domain"
				);
			}
			// }
			echo '{"ok":1,"message":"'.addslashes($email).'"}';
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
