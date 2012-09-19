<?php
/**
	* user authentication
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (isset($_REQUEST['action']) && $_REQUEST['action']=='login'
	&& isset($_REQUEST['email']) && isset($_REQUEST['password'])
) {
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
		// { update location
		if ($r['location_lat'] || $r['location_lng']) {
			$_SESSION['location']=array(
				'lat'=>$r['location_lat'],
				'lng'=>$r['location_lng'],
				'locid'=>0,
				'locname'=>''
			);
			require_once dirname(__FILE__).'/api-funcs.php';
			$locations=Core_locationsGet();
			if (count($locations)) {
				foreach ($locations as $loc) {
					if ($loc['lat']==$r['location_lat']
						&& $loc['lng']==$r['location_lng']
					) {
						$_SESSION['location']=array(
							'lat'=>$loc['lat'],
							'lng'=>$loc['lng'],
							'locid'=>$loc['id'],
							'locname'=>$loc['name']
						);
					}
				}
			}
		}
		// }
		// { redirect if applicable
		$redirect_url='';
		if (isset($_POST['login_referer'])
			&& strpos($_POST['login_referer'], '/')===0
		) {
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
	$fname=USERBASE.'/ww.cache/user-session-resets/'.$_SESSION['userdata']['id'];
	if (file_exists($fname)) {
		$_SESSION['userdata'] = dbRow(
			'select * from user_accounts where id='.$_SESSION['userdata']['id']
		);
		unlink($fname);
	}
	if (!isset($_SESSION['userdata']['groups'])) {
		$USERGROUPS = array();
		$rs = dbAll(
			"select id,name from users_groups,groups where id=groups_id and "
			."user_accounts_id=" . $_SESSION['userdata']['id']
		);
		if ($rs) {
			foreach ($rs as $r) {
				$USERGROUPS[$r['name']] = $r['id'];
			}
		}
		$_SESSION['userdata']['groups']=$USERGROUPS;
		if (isset($_SESSION['userdata']['groups']['administrators'])) {
			$_SESSION['wasAdmin']=true;
		}
	}
}
if (isset($_REQUEST['logout'])) {
	unset($_SESSION['userdata']);
}
