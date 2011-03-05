<?php
if (isset($_REQUEST['action']) && $_REQUEST['action']=='login') {
	// { variables
	$email=$_REQUEST['email'];
	$password=$_REQUEST['password'];
	// }
	$r=dbRow(
		'select * from user_accounts where email="'.addslashes($email)
		.'" and password=md5("'.$password.'")'
	);
	if ($r && count($r)) {
		// { update session variables
		$r['password']=$password;
		$_SESSION['userdata'] = $r;
		dbQuery('update user_accounts set last_login=now() where id='.$r['id']);
		// }
		// { redirect if applicable
		$redirect_url='';
		if (isset($_POST['login_referer'])
			&& strpos($_POST['login_referer'],'/')===0
		){
			$redirect_url=$_POST['login_referer'];
		}
		else if (isset($PAGEDATA) && $PAGEDATA->vars['userlogin_redirect_to']) {
			$p=Page::getInstance($PAGEDATA->vars['userlogin_redirect_to']);
			$redirect_url=$p->getRelativeUrl();
		}
		if (isset($no_redirect)) {
			return;
		}
		if ($redirect_url!='') {
			redirect($redirect_url);
		}
		// }
	}
}
if (isset($_SESSION['userdata']['id'])) {
	dbQuery(
		'update user_accounts set last_view=now() where id='
		.$_SESSION['userdata']['id']
	);
	if (!isset($_SESSION['userdata']['groups'])) {
		$USERGROUPS = array();
		$rs = dbAll("select id,name from users_groups,groups where id=groups_id and user_accounts_id=" . $_SESSION['userdata']['id']);
		if ($rs) {
			foreach ($rs as $r) {
				$USERGROUPS[$r['name']] = $r['id'];
			}
		}
		$_SESSION['userdata']['groups']=$USERGROUPS;
	}
}
if (isset($_REQUEST['logout'])) {
	unset($_SESSION['userdata']);
}
