<?php
/**
	* page type for Privacy, for handling user logins and registrations
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Privacy_controller

/**
	* function for displaying the registration/reminder/login forms
	*
	* @return string HTML of the forms
	*/
function Privacy_controller() {
	// { variables
	$action=@$_REQUEST['action'];
	$c='';
	global $loggedin, $sitedomain, $DBVARS, $PAGEDATA;
	// }
	if (@$_GET['hash'] && $_GET['email']) {
		$r=dbRow(
			"select * from user_accounts where email='".addslashes($_GET['email'])
			."' and verification_hash='".addslashes($_GET['hash'])."'"
		);
		if (!count($r)) {
			die('that hash and email combination does not exist');
		}
		dbQuery(
			"update user_accounts set verification_hash='',active=1 where ema"
			."il='".addslashes($_GET['email'])."' and verification_hash='"
			.addslashes($_GET['hash'])."'"
		);
		Core_mail(
			$_GET['email'],
			'['.$sitedomain.'] user verified',
			"Thank you,<br/><br/>your user account with us has now been verified. You"
			." can login now using your email address and password.",
			"noreply@$sitedomain"
		);
		return '<p>Thank you for registering.</p><p>Your account has now been'
			.' verified.</p><p>Please <a href="/_r?type=privacy">click here</a>'
			.' to login.</p>';
		$action='Login';
		$_REQUEST['email']=$_GET['email'];
		$_REQUEST['password']=$password;
	}
	if ($action=='Login' || $loggedin) {
		// { variables
		if ($loggedin) {
			$email=$_SESSION['userdata']['email'];
			$password=$_SESSION['userdata']['password'];
		}
		else {
			$email=$_REQUEST['email'];
			$password=$_REQUEST['password'];
		}
		// }
		$sql='select * from user_accounts where email="'.$email
			.'" and password=md5("'.$password.'") limit 1';
		$r=dbRow($sql);
		if ($r) {
			// { update session variables
			$loggedin=1;
			$r['password']=$password;
			$_SESSION['userdata']=$r;
			// }
			$n=$_SESSION['userdata']['name'];
			dbQuery('update user_accounts set last_view=now() where id='.$r['id']);
			if ($action=='Login') {
				$redirect_url='';
				if (isset($_REQUEST['login_referer'])
					&& strpos($_REQUEST['login_referer'], '/')===0
				) {
					$redirect_url=$_REQUEST['login_referer'];
				}
				elseif(@$PAGEDATA->vars['userlogin_redirect_to']) {
					$p=Page::getInstance($PAGEDATA->vars['userlogin_redirect_to']);
					$redirect_url=$p->getRelativeUrl();
				}
				dbQuery(
					'update user_accounts set last_login=now() where id='.$r['id']
				);
				if ($redirect_url!='') {
					redirect($redirect_url);
				}
			}
			return Privacy_profileGet();
		}
		else {
			unset($_SESSION['userdata']);
		}
	}
	if ($c=='') {
		$c=$PAGEDATA->render();
	}
	if ($action=='Remind') {
		// { variables
		$email=@$_REQUEST['email'];
		// }
		$r=dbOne('select id from user_accounts where email="'.$email.'"', 'id');
		if ($r) {
			$p=Password::getNew();
			Core_mail(
				$email,
				'['.$sitedomain.'] user password changed',
				"Your new password:<br/><br/>".$p,
				"noreply@$sitedomain"
			);
			dbQuery(
				'update user_accounts set password=md5("'.$p.'") where email="'
				.$email.'"'
			);
			$c.='<script defer="defer">$(function(){$("<strong>Please'
				.' check your email for your new password.</strong>")'
				.'.dialog({modal:true,height:100,width:150});});</script>';
		}
		else {
			$c.='<script>$(function(){$("<strong>No user account with that email '
				.'address exists.</strong>").dialog({modal:true,height:100,width:15'
				.'0});});</script>';
		}
	}
	if (!isset($PAGEDATA->vars['userlogin_visibility'])
		||!$PAGEDATA->vars['userlogin_visibility']
	) {
		$PAGEDATA->vars['userlogin_visibility']=3;
	}
	if (!$loggedin) { // show login and registration box
		$c.='<div class="tabs"><ul>';
		// { menu
		if ($PAGEDATA->vars['userlogin_visibility']&1) {
			$c.='<li><a href="#Privacy_controllerLoginBox">'.__('Login', 'core')
				.'</a></li>'
				.'<li><a href="#userPasswordReminder">'.__('Password reminder', 'core')
				.'</a></li>';
		}
		if ($PAGEDATA->vars['userlogin_visibility']&2) {
			$c.='<li><a href="#userregistration">'.__('Register', 'core').'</a></li>';
		}
		// }
		$c.='</ul>';
		// { tabs
		if ($PAGEDATA->vars['userlogin_visibility']&1) {
			$c.=Privacy_loginForm().Privacy_passwordReminderForm();
		}
		if ($PAGEDATA->vars['userlogin_visibility']&2) {
			$c.=Privacy_registrationController();
		}
		// }
		$c.='</div>';
	}
	return $c;
}

// }
// { Privacy_loginForm

/**
	* function for displaying a login form
	*
	* @return string HTML of the form
	*/
function Privacy_loginForm() {
	global $PAGEDATA;
	$c='<div id="Privacy_controllerLoginBox">';
	if (@$_REQUEST['action']=='Login') {
		$c.='<em>'.__('incorrect email or password given.').'</em>';
	}
	if (isset($PAGEDATA->vars['userlogin_message_login'])) {
		$c.=$PAGEDATA->vars['userlogin_message_login'];
	}
	$c.='<form class="userLoginBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#tab=Login" method="post"><table>';
	$c.='<tr><th><label for="email">'.__('Email', 'core').'</label></th>'
		.'<td><input name="email" '
		.'value="'.@$_REQUEST['email'].'" /></td>';
	$c.='<th><label for="password">'.__('Password', 'core').'</label></th>'
		.'<td><input type="password" name="password" /></td></tr>';
	$c.='</table>'
		.'<button>'.__('Login', 'core').'</button>'
		.'<input type="hidden" name="action" value="Login"/>';
	if (isset($_REQUEST['login_referer'])) {
		$c.='<input type="hidden" name="login_referer" value="'
			.htmlspecialchars($_REQUEST['login_referer'], ENT_QUOTES).'" />';
	}
	$c.='</form></div>';
	return $c;
}

// }
// { Privacy_passwordReminderForm

/**
	* form for a user to get a password reminder (a token)
	*
	* @return string HTML of the form
	*/
function Privacy_passwordReminderForm() {
	global $PAGEDATA;
	$c='<div id="userPasswordReminder">';
	if (isset($PAGEDATA->vars['userlogin_message_reminder'])) {
		$c.=$PAGEDATA->vars['userlogin_message_reminder'];
	}
	$c.='<form class="userLoginBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#tab=Password Reminder" method="post"><table>';
	$c.='<tr><th><label for="email">'.__('Email', 'core').'</label></th>'
		.'<td><input name="email"/></td></tr></table>';
	$c.='<button>'.__('Remind', 'core').'</button>'
		.'<input type="hidden" name="action" value="Remind" /></form></div>';
	return $c;
}

// }
// { Privacy_registrationController

/**
	* either display the user registration form, or handle the registration
	*
	* @return string one or d'other
	*/
function Privacy_registrationController() {
	if (@$_REQUEST['a']=='Register') {
		return Privacy_registrationRegister();
	}
	return Privacy_registrationShowForm();
}

// }
// { Privacy_registrationShowForm

/**
	* show a registration form for creating a user
	*
	* @param string $error any error messages that need to be displayed
	* @param string $alert any messages that need to be displayed in popups
	*
	* @return string HTML of the form
	*/
function Privacy_registrationShowForm($error='', $alert='') {
	global $PAGEDATA;

	/**
	 * form validation array
	 */
	$validation=array();

	$c='<div id="userregistration"><em style="color:red" id="error"></em>';
	if (isset($PAGEDATA->vars['userlogin_message_registration'])) {
		$c.=$PAGEDATA->vars['userlogin_message_registration'];
	}
	require_once SCRIPTBASE.'ww.incs/recaptcha.php';
	$c.=$error.'<form id="reg-form" class="userRegistrationBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#userregistration" method="post"><table>'
		.'<tr><th>'.__('Name', 'core').'</th>'
		.'<td><input type="text" name="name" value="'
		.htmlspecialchars(@$_REQUEST['name']).'" /></td>'
		.'<th>'.__('Email', 'core').'</th>'
		.'<td><input type="text" name="email" value="'
		.htmlspecialchars(@$_REQUEST['email']).'" /></td></tr>'
		.'<tr><th>'.__('Preferred Password', 'core').'</th>'
		.'<td><input name="pass1" type="password"/></td>'
		.'<th>'.__('Repeat Password', 'core').'</th>'
		.'<td><input name="pass2" type="password"/></td'
		.'></tr><tr><td colspan="2">'.Recaptcha_getHTML().'</td></tr></table>';
	if (strlen(@$PAGEDATA->vars['privacy_extra_fields'])>2) {
		$c.='<table>';
		$required=array();
		$rs=json_decode($PAGEDATA->vars['privacy_extra_fields']);
		$cnt=0;
		foreach ($rs as $r) {
			if (!$r->name || $r->type=='hidden') {
				continue;
			}
			$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r->name);
			$class='';
			if (isset($r->is_required) && $r->is_required) {
				$required[]=$name.','.$r->type;
				$class=' required';
				$validation[ 'privacy_extras_'.$name ] = array('required' => true);
			}
			if (isset($_REQUEST[$name])) {
				$_SESSION['privacys'][$name]=$_REQUEST[$name];
			}
			$val=@$_REQUEST[$name];
			if (!$val && isset($_SESSION['userdata']) && $_SESSION['userdata']) {
				switch ($name) {
					case 'Email': case '__ezine_subscribe': // {
						$val=$_SESSION['userdata']['email'];
					break; // }
					case 'FirstName': // {
						$val=preg_replace('/ .*/', '', $_SESSION['userdata']['name']);
					break; // }
					case 'Street': // {
						$val=$_SESSION['userdata']['address1'];
					break; // }
					case 'Street2': // {
						$val=$_SESSION['userdata']['address2'];
					break; // }
					case 'Surname': // {
						$val=preg_replace('/.* /', '', $_SESSION['userdata']['name']);
					break; // }
					case 'Town': // {
						$val=$_SESSION['userdata']['address3'];
					break; // }
				}
			}
			if (!isset($_REQUEST[$name])) {
				$_REQUEST[$name]='';
			}
			switch($r->type){
				case 'checkbox': // {
					$d='<input type="checkbox" id="privacy_extras_'.$name
						.'" name="privacy_extras_'.$name.'"';
					if ($_REQUEST[$name]) {
						$d.=' checked="'.$_REQUEST[$name].'"';
					}
					$d.=' class="'.$class.' checkbox" />';
				break; // }
				case 'ccdate': // {
					if ($_REQUEST[$name]=='') {
						$_REQUEST[$name]=date('Y-m');
					}
					$d='<input name="privacy_extras_'.$name.'" value="'
						.$_REQUEST[$name].'" class="ccdate" />';
				break; // }
				case 'date': // {
					if ($_REQUEST[$name]=='') {
						$_REQUEST[$name]=date('Y-m-d');
					}
					$d='<input name="privacy_extras_'.$name.'" value="'
						.$_REQUEST[$name].'" class="date" />';
				break; // }
				case 'email': // {
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" value="'.$val.'" class="email'.$class.' text" />';
					if (isset($validation[ 'privacy_extras_'.$name ])) {
						$validation[ 'privacy_extras_'.$name ][ 'email' ] = true;
					}
					else {
						$validation[ 'privacy_extras_'.$name ] = array('email' => true);
					}
				break; // }
				case 'url': // {
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" value="" class="url'.$class.' text" />';
					if (isset($validation[ 'privacy_extras_'.$name ])) {
						$validation[ 'privacy_extras_'.$name ][ 'url' ] = true;
					}
					else {
						$validation[ 'privacy_extras_'.$name ] = array('url' => true);
					}
				break; // }
				case 'file': // {
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" type="file" />';
				break; // }
				case 'hidden': // {
					$d='<textarea id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" class="'.$class.' hidden">'
						.htmlspecialchars($r->extra).'</textarea>';
				break; // }
				case 'selectbox': // {
					$d='<select id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'">';
					$arr=explode("\n", htmlspecialchars($r->extra));
					foreach ($arr as $li) {
						if ($_REQUEST[$name]==$li) {
							$d.='<option selected="selected">'.rtrim($li).'</option>';
						}
						else {
							$d.='<option>'.rtrim($li).'</option>';
						}
					}
					$d.='</select>';
				break; // }
				case 'textarea': // {
					$d='<textarea id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" class="'.$class.'">'.$_REQUEST[$name].'</textarea>';
				break; // }
				default: // { input boxes, and anything which was not handled already
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'
						.$name.'" value="'.$val.'" class="'.$class.' text" />';
					// }
			}
			$c.='<tr><th>'.htmlspecialchars($r->name);
			if (isset($r->is_required) && $r->is_required) {
				$c.='<sup>*</sup>';
			}
			$c.="</th>\n\t<td>".$d."</td></tr>\n\n";
			$cnt++;
		}
		$c.='</table>';
		if (count($required)) {
			$c.='<br /><span>'.__('* indicates required fields', 'core').'</span>';
		}
	}
	if (@$PAGEDATA->vars['userlogin_terms_and_conditions']) {
		$c.='<input type="checkbox" name="terms_and_conditions" /> <span>'
			.__(
				'I agree to the <a href="javascript:userlogin_t_and_c()">'
				.'terms and conditions</a>.', 'core'
			)
			.'</span><br />';
		$c.='<script defer="defer">function userlogin_t_and_c(){$("<div>'
			.addslashes(
				str_replace(
					array("\n", "\r"), ' ',
					$PAGEDATA->vars['userlogin_terms_and_conditions']
				)
			)
			.'</div>").dialog({modal:true,width:"90%"});}</script>';
	}
	if ($alert) {
		WW_addInlineScript(
			'$(function(){$(\'<div>'.addslashes($alert)
			.'</div>\').dialog({modal:true});});'
		);
	}
	$c.='<button id="registration-submit">'.__('Register', 'core').'</button>'
		.'<input type="hidden" name="a" value="Register" /></form></div>';

	/** 
	 * add jquery form validation
	 */
	WW_addScript('/j/validate.jquery.min.js');
	$script = ' 
			var options = ' . json_encode($validation) . ';

			$( "#reg-form" ).validate( options, function( message ){
		$( "#userregistration em#error" ).html( message );
	} );
	';
	WW_addInlineScript($script);
	$c .= '<style type="text/css">.error{ border:1px solid #600;'
		.'background:#f99 }</style>'; 
	return $c;
}

// }
// { Privacy_registrationRegister

/**
	* check a registration submission, and register the user if valid
	*
	* @return string either the registration form again, or a success message
	*/
function Privacy_registrationRegister() {
	global $DBVARS, $PAGEDATA;
	// { variables
	$name=@$_REQUEST['name'];
	$email=@$_REQUEST['email'];
	$usertype=@$_REQUEST['usertype'];
	$address1=@$_REQUEST['address1'];
	$address2=@$_REQUEST['address2'];
	$address3=@$_REQUEST['address3'];
	$howyouheard=@$_REQUEST['howyouheard'];
	$pass1=$_REQUEST['pass1'];
	$pass2=$_REQUEST['pass2'];
	// }
	if (@$PAGEDATA->vars['userlogin_terms_and_conditions']
		&& !isset($_REQUEST['terms_and_conditions'])
	) {
		return '<em>'
			.__(
				'You must agree to the terms and conditions.'
				.' Please press "Back" and try again.', 'core'
			)
			.'</em>';
	}
	$missing=array();
	// { check for user_account table "extras"
	$extras=array();
	if (@$PAGEDATA->vars['privacy_extra_fields']) {
		$rs=json_decode($PAGEDATA->vars['privacy_extra_fields']);
		if ($rs) {
			foreach ($rs as $r) {
				if (!$r->name) {
					continue;
				}
				$ename=preg_replace('/[^a-zA-Z0-9_]/', '', $r->name);
				$extras[$r->name]=isset($_REQUEST['privacy_extras_'.$ename])
					?$_REQUEST['privacy_extras_'.$ename]
					:'';
				if ($extras[$r->name]=='' && @$r->is_required) {
					$missing[]=$r->name;
				}
			}
		}
	}
	// }
	// { check for required fields
	if (!$name) {
		$missing[]='<span>'.__('your name', 'core').'</span>';
	}
	if (!$email) {
		$missing[]='<span>'.__('your email address', 'core').'</span>';
	};
	if (count($missing)) {
		return Privacy_registrationShowForm(
			'<em><span>'.__('You must fill in the following fields:', 'core')
			.'</span> '.join(', ', $missing).'</em>'
		);
	}
	// }
	// { check if the email address is already registered
	$r=dbRow('select id from user_accounts where email="'.$email.'"');
	if ($r && count($r)) {
		return Privacy_registrationShowForm(
			'<p><em>'.__('That email is already registered.', 'core').'</em></p>'
		);
	}
	// }
	// { check that passwords match
	if (!$pass1 || $pass1!=$pass2) {
		return Privacy_registrationShowForm(
			'<p><em>'.__('Please enter your preferred password twice', 'core')
			.'</em></p>'
		);
	}
	// }
	// { check captcha
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/recaptcha.php';
	if (!isset($_REQUEST['recaptcha_challenge_field'])) {
		return Privacy_registrationShowForm(
			'<p><em>'.__('You must fill in the Captcha', 'core').'</em></p>'
		);
	}
	else {
		$result 
			= recaptcha_check_answer(
				RECAPTCHA_PRIVATE,
				$_SERVER['REMOTE_ADDR'],
				$_REQUEST['recaptcha_challenge_field'],
				$_REQUEST['recaptcha_response_field']
			);
		if (!$result->is_valid) {
			return Privacy_registrationShowForm(
				'<p><em>'.__('Invalid captcha. Please try again.', 'core').'</em></p>'
			);
		}
	}
	// }
	// { register the account
	$password=$pass1;
	$r=dbRow("SELECT * FROM site_vars WHERE name='user_discount'");
	$discount=(float)$r['value'];
	$hash=base64_encode(sha1(rand(0, 65000), true));
	$sql='insert into user_accounts set name="'.$name.'", password=md5("'
		.$password.'"), email="'.$email.'", verification_hash="'.$hash
		.'", active=0, extras="'.addslashes(json_encode($extras))
		.'",date_created=now()';
	dbQuery($sql);
	$page=$GLOBALS['PAGEDATA'];
	$id=dbOne('select last_insert_id() as id', 'id');
	if (isset($page->vars['userlogin_groups'])) {
		$gs=json_decode($page->vars['userlogin_groups'], true);
		foreach ($gs as $k=>$v) {
			dbQuery(
				"insert into users_groups set user_accounts_id=$id,groups_id="
				.(int)$k
			);
		}
	}
	$sitedomain=$_SERVER['HTTP_HOST'];
	$long_url="http://$sitedomain".$page->getRelativeUrl()."?hash="
		.urlencode($hash)."&email=".urlencode($email).'#Login';
	$short_url=md5($long_url);
	$lesc=addslashes($long_url);
	$sesc=urlencode($short_url);
	dbQuery(
		'insert into short_urls set cdate=now(),long_url="'
		.addslashes($long_url).'",short_url="'.$short_url.'"'
	);
	if (@$page->vars['userlogin_registration_type']=='Email-verified') {
		Core_mail(
			$email,
			'['.$sitedomain.'] user registration',
			"Hello!<br/><br/>This message is to verify your email address, which has "
			."been used to register a user-account on the $sitedomain website."
			."<br/><br/>After clicking the link below, you will be logged into the se"
			."rver.<br/><br/>If you did not register this account, then please delete"
			." this email. Otherwise, please click the following URL to verify "
			."your email address with us. Thank you.<br/><br/>http://$sitedomain/_s/"
			.$sesc,
			"noreply@$sitedomain"
		);
		if (1 || $page->vars['userlogin_send_admin_emails']) {
			$admins=dbAll(
				'select email from user_accounts,users_groups where groups_id=1 &'
				.'& user_accounts_id=user_accounts.id'
			);
			foreach ($admins as $admin) {
				Core_mail(
					$admin['email'],
					'['.$sitedomain.'] user registration',
					"Hello!<br/><br/>This message is to alert you that a user ($email) ha"
					."s been created on your site, http://$sitedomain/ - the user h"
					."as not yet been activated, so please log into the admin area "
					."of the site (http://$sitedomain/ww.admin/ - under Site Option"
					."s then Users) and verify that the user details are correct.",
					"noreply@$sitedomain"
				);
			}
		}
		return Privacy_registrationShowForm(
			false,
			'<p><strong>'.__('Thank you for registering.', 'core')
			.'</strong> '
			.__(
				'Please check your email for a verification URL.'
				.' Once that\'s been followed, your account will be activated.', 'core'
			)
			.'</p>'
		);
	}
	else {
		$admins=dbAll(
			'select email from user_accounts,users_groups where groups_id=1 && '
			.'user_accounts_id=user_accounts.id'
		);
		foreach ($admins as $admin) {
			Core_mail(
				$admin['email'],
				'['.$sitedomain.'] user registration',
				"Hello!<br/><br/>This message is to alert you that a user ($email) has "
				."been created on your site, http://$sitedomain/ - the user has n"
				."ot yet been activated, so please log into the admin area of the"
				." site (http://$sitedomain/ww.admin/ - under Site Options then U"
				."sers) and verify that the user details are correct.",
				"noreply@$sitedomain"
			);
		}
		return Privacy_registrationShowForm(
			false,
			'<p><strong>'.__('Thank you for registering.').'</strong> '
			.__(
				'Our admins will moderate your registration,'
				.' and you will receive an email when it is activated.'
			)
			.'</p>'
		);
	}
	// }
}

// }
// { Privacy_profileGet

/**
	* display a user's profile
	*
	* @return string HTML of the profile
	*/
function Privacy_profileGet() {
	$uid = addslashes($_SESSION['userdata'][ 'id' ]);
	$user = dbRow('select * from user_accounts where id=' . $uid);
	$html=Core_trigger('privacy_overload', array($user));
	if ($html) {
		return $html;
	}
	$contact=json_decode($user['contact'], true);

	$phone=(!isset($contact['phone']) || $contact['phone']=='')
		?'<a href="javascript:edit_user_dialog('.$user['id']
		.');">'.__('Add', 'core').'</a>'
		:htmlspecialchars($contact['phone']);

	// get array of groups the user is a member of
	$groups = array();
	$group_ids = dbAll(
		'select groups_id from users_groups where user_accounts_id=' . $uid
	);
	
	$remainingCreditsJson=dbOne(
		'select * from user_accounts where id='.$uid.' limit 1',
		'extras'
	);
	$remainingCredits = (int)json_decode($remainingCreditsJson)->{'free-credits'};
	
	if ($remainingCredits==0) { // the user has not been initialised	  
		$remainingCredits=dbOne(
			'SELECT * FROM `site_vars` WHERE `name`="max-free-credits"',
			'value'
		);
		$extras=dbOne(
			'select * from user_accounts where id='.$uid.' limit 1',
			'extras'
		);
		$extras=json_decode($extras, true);
		$extras['free-credits']=$remainingCredits;
		dbQuery(
			'update user_accounts set extras="'.json_encode($extras).'" where id='
			.$user['id']
		);
	}
	foreach ($group_ids as $key => $id) {
		array_push(
			$groups,
			dbOne('select name from groups where id=' . $id[ 'groups_id' ], 'name')
		);
	}
	$groups = implode(',', $groups);	
	$html='<a class="logout" href="/?logout=1" style="float:right">'.__('Logout')
		.'</a><h2>'.htmlspecialchars($user['name']).'</h2>'
		.'<div id="tabs"><ul>'
		.'<li><a href="#details">'.__('User Details', 'core').'</a></li>'
		.'<li><a href="#address">'.__('Address', 'core').'</a></li>'
		.'</ul>'
		.'<div id="details"><p style="float:right">'
		.'<a href="javascript:edit_user_dialog('.$user['id'].');"'
		.' id="edit-user-info">'.__('Edit Details', 'core').'</a>'
		.' <a href="javascript:change_password_dialog('.$user['id'].');"'
		.' id="user-change-password" style="diplay:inline">'
		.__('Change Password', 'core').'</a></p>'
		.'<table id="user-info" style="border:1px solid #ccc;margin:10px">'
		.'<tr><th>'.__('Email', 'core').'</th><td>'.htmlspecialchars($user['email'])
		.'</td></tr>'
		.'<tr><th>'.__('Phone', 'core').'</th><td>'.$phone.'</td></tr>'
		.'<tr><th>'.__('Avatar', 'core').'</th><td><span id="avatar-wrapper"'
		.' data-uid="'.$uid.'"></span></td></tr>'
		.'<tr><th>'.__('RemainingCredits', 'core').'</th><td>'.$remainingCredits
		.'</td></tr>'
		.'</table></div>'
		.'<div id="address"><a id="new-address" href="javascript:add_address();"'
		.' style="float:right">[+]'.__('Add Address').'</a>'
		.'<div id="address-container"><table>';
	if ($addresses=json_decode(@$user['address'], true)) {
		foreach ($addresses as $name=>$address) {
		  $select=(@$address['default']=='yes')?' checked="checked"':'';
		  $html.=' <tr> <td> <input type="radio"'.$select
				.' name="default-address" value="'.$name.'"/> </td> <td>'
				.str_replace(' ', '-', $name).'</td> <td> <a href="javascript:edit_addr'
				.'ess(\''.$name.'\');" class="edit-addr" name="'.$name
				.'">'.__('edit').'.</a> <a href="javascript:;" '
				.'class="delete-addr" name="'
				.$name.'">'.__('[x]').'</a> </td> </tr> ';
		}
	}
	else {
		$html.= '<i>'.__('No address(es) saved yet', 'core').'</i>';
	}
	$html.='</table></div><br style="clear:both"/></div>
	</div>';
	WW_addScript('privacy/js.js');
	WW_addScript('privacy/frontend/change_password.js');
	$html .= Core_trigger('privacy_user_profile', array($user));
	return $html;
}

// }

// if not logged in display login box
if (!isset($_SESSION['userdata']['id'])) {
	$html=Privacy_controller();
	WW_addInlineScript('$(function(){$(".tabs").tabs()});');
}
// else show profile
else {
	$html=Privacy_profileGet();	
}
