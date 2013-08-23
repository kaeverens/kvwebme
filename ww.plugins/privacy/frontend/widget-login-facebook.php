<?php
/**
	* login via facebook
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (isset($_REQUEST['error']) || !isset($_REQUEST['code'])) {
	header('Location: /');
	Core_quit();
}

list($pid, $wid)=explode(
	'-',
	preg_replace(
		'/.*widget-id=([0-9]*-[0-9]*).*/',
		'\1',
		$_SERVER['REQUEST_URI']
	)
);

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$panel=json_decode(
	dbOne('select body from panels where id='.((int)$pid), 'body')
);

foreach ($panel->widgets as $widget) {
	if ($widget->id==$wid) {
		$fbappid=$widget->fbappid;
		$fbsecret=$widget->fbsecret;
		$url='https://graph.facebook.com/oauth/access_token'
			.'?client_id='.$fbappid
			.'&redirect_uri=http://'.$_SERVER['HTTP_HOST'].preg_replace(
				'/\?.*/',
				'',
				$_SERVER['REQUEST_URI']
			)
			.'&client_secret='.$fbsecret
			.'&code='.$_REQUEST['code'];
		$auth=file_get_contents($url);
		$details=file_get_contents('https://graph.facebook.com/me?'.$auth);
		$details=json_decode($details);
		if (is_null($details)) {	// failed login
			mail(DistConfig::get('email'), 'Facebook failed data', $details);
			header('Location: /');
			Core_quit();
		}
		$name= $details->name;
		$email=$details->email;
		$user=dbRow(
			'select * from user_accounts where email="'.addslashes($email).'"'
		);
		if ($user==false) {
			$pass=md5($details->id);
			dbQuery(
				'insert into user_accounts set email="'.addslashes($email)
				.'",name="'.addslashes($name).'",active=1,password="'.$pass.'"'
			);
			$user=dbRow('select * from user_accounts where id='.dbLastInsertId());
		}
		$_SESSION['userdata'] = $user;
		dbQuery('update user_accounts set last_login=now() where id='.$user['id']);
	}
}
header('Location: /');
