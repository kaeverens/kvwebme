<?php
function userloginandregistrationDisplay() {
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
		if (!isset($_REQUEST['np'])) {
			$password=Password::getNew();
			$password='password=md5(\''.$password.'\'),';
		}
		dbQuery(
			"update user_accounts set $np verification_hash='',active=1 where ema"
			."il='".addslashes($_GET['email'])."' and verification_hash='"
			.addslashes($_GET['hash'])."'"
		);
		if (isset($_REQUEST['np'])) {
			mail(
				$_GET['email'],
				'['.$sitedomain.'] user verified',
				"Thank you,\n\nyour user account with us has now been verified. You"
				." can login now using your email address and password.",
				"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
			);
			return '<p>Thank you for registering.</p><p>Your account has now been'
				.' verified.</p><p>Please <a href="/_r?type=privacy">click here</a>'
				.' to login.</p>';
		}
		else {
			mail(
				$_GET['email'],
				'['.$sitedomain.'] user password created',
				"Your new password:\n\n".$password,
				"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
			);
		}
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
			return userregistration_showProfile();
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
			mail(
				$email,
				'['.$sitedomain.'] user password changed',
				"Your new password:\n\n".$p,
				"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
			);
			dbQuery(
				'update user_accounts set password=md5("'.$p.'") where email="'
				.$email.'"'
			);
			$c.='<script>$(function(){$("<strong>Please check your email for your'
				.' new password.</strong>").dialog({modal:true,height:100,width:150'
				.'});});</script>';
		}
		else {
			$c.='<script>$(function(){$("<strong>No user account with that email '
				.'address exists.</strong>").dialog({modal:true,height:100,width:15'
				.'0});});</script>';
		}
	}
	if (!isset($PAGEDATA->vars['userlogin_visibility'])
		||$PAGEDATA->vars['userlogin_visibility']
	) {
		$PAGEDATA->vars['userlogin_visibility']=3;
	}
	if (!$loggedin) { // show login and registration box
		$c.='<div class="tabs"><ul>';
		// { menu
		if ($PAGEDATA->vars['userlogin_visibility']&1) {
			$c.='<li><a href="#userLoginBoxDisplay">Login</a></li>';
			$c.='<li><a href="#userPasswordReminder">Password reminder</a></li>';
		}
		if ($PAGEDATA->vars['userlogin_visibility']&2) {
			$c.='<li><a href="#userregistration">Register</a></li>';
		}
		// }
		$c.='</ul>';
		// { tabs
		if ($PAGEDATA->vars['userlogin_visibility']&1) {
			$c.= userLoginBoxDisplay();
			$c.=userPasswordReminder();
		}
		if ($PAGEDATA->vars['userlogin_visibility']&2) {
			$c.=userregistration();
		}
		// }
		$c.='</div>';
	}
	return $c;
}
function userLoginBoxDisplay() {
	global $PAGEDATA;
	$c='<div id="userLoginBoxDisplay">';
	if (@$_REQUEST['action']=='Login') {
		$c.='<em>incorrect email or password given.</em>';
	}
	if (isset($PAGEDATA->vars['userlogin_message_login'])) {
		$c.=$PAGEDATA->vars['userlogin_message_login'];
	}
	$c.='<form class="userLoginBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#tab=Login" method="post"><table>';
	$c.='<tr><th><label for="email">Email</label></th><td><input name="email" '
		.'value="'.@$_REQUEST['email'].'" /></td>';
	$c.='<th><label for="password">Password</label></th><td><input '
		.'type="password" name="password" /></td></tr>';
	$c.='</table><input type="submit" name="action" value="Login" />';
	if (isset($_REQUEST['login_referer'])) {
		$c.='<input type="hidden" name="login_referer" value="'
			.htmlspecialchars($_REQUEST['login_referer'], ENT_QUOTES).'" />';
	}
	$c.='</form>';
	$c.='</div>';
	return $c;
}
function userPasswordReminder() {
	global $PAGEDATA;
	$c='<div id="userPasswordReminder">';
	if (isset($PAGEDATA->vars['userlogin_message_reminder'])) {
		$c.=$PAGEDATA->vars['userlogin_message_reminder'];
	}
	$c.='<form class="userLoginBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#tab=Password Reminder" method="post"><table>';
	$c.='<tr><th><label for="email">Email</label></th><td><input name="email"'
		.'/></td></tr></table>';
	$c.='<input type="submit" name="action" value="Remind" /></form>';
	$c.='</div>';
	return $c;
}
function userregistration() {
	if (@$_REQUEST['a']=='Register') {
		return userregistration_register();
	}
	return userregistration_form();
}
function userregistration_form($error='', $alert='') {
	global $PAGEDATA;

	/**
	 * form validation array
	 */
	$validation = array( );

	$c='<div id="userregistration"><em style="color:red" id="error"></em>';
	if (isset($PAGEDATA->vars['userlogin_message_registration'])) {
		$c.=$PAGEDATA->vars['userlogin_message_registration'];
	}
	$c.=$error.'<form id="reg-form" class="userRegistrationBox" action="'
		.$GLOBALS['PAGEDATA']->getRelativeUrl()
		.'#userregistration" method="post"><table>'
		.'<tr><th>Name</th><td><input type="text" name="name" value="'
		.htmlspecialchars(@$_REQUEST['name']).'" /></td>'
		.'<th>Email</th><td><input type="text" name="email" value="'
		.htmlspecialchars(@$_REQUEST['email']).'" /></td></tr>'
		.'<tr><th>Preferred Password</th><td><input name="pass1" type="password"'
		.'/></td>'
		.'<th>Repeat Password</th><td><input name="pass2" type="password" /></td'
		.'></tr></table>';
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
			$c.='<br />* indicates required fields';
		}
	}
	if (@$PAGEDATA->vars['userlogin_terms_and_conditions']) {
		$c.='<input type="checkbox" name="terms_and_conditions" /> I agree to t'
			.'he <a href="javascript:userlogin_t_and_c()">terms and conditions</a'
			.'>.<br />';
		$c.='<script>function userlogin_t_and_c(){$("<div>'
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
	$c.='<input type="submit" name="a" id="registration-submit" value="Regist'
		.'er" /></form></div>';

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
function userregistration_register() {
	global $DBVARS, $PAGEDATA;
	// { variables
	$name=@$_REQUEST['name'];
	$email=@$_REQUEST['email'];
	$phone=@$_REQUEST['phone'];
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
		return '<em>You must agree to the terms and conditions. Please press "B'
			.'ack" and try again.</em>';
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
		$missing[]='your name';
	}
	if (!$email) {
		$missing[]='your email address';
	};
	if (count($missing)) {
		return userregistration_form(
			'<em>You must fill in the following fields: '.join(', ', $missing).'</em>'
		);
	}
	// }
	// { check if the email address is already registered
	$r=dbRow('select id from user_accounts where email="'.$email.'"');
	if ($r && count($r)) {
		return userregistration_form(
			'<p><em>That email is already registered.</em></p>'
		);
	}
	// }
	// { check that passwords match
	if (!$pass1 || $pass1!=$pass2) {
		return userregistration_form(
			'<p><em>Please enter your preferred password twice</em></p>'
		);
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
	dbQuery("insert into short_urls values(0,now(),'$lesc','$short_url')");
	if (@$page->vars['userlogin_registration_type']=='Email-verified') {
		mail(
			$email,
			'['.$sitedomain.'] user registration',
			"Hello!\n\nThis message is to verify your email address, which has "
			."been used to register a user-account on the $sitedomain website."
			."\n\nAfter clicking the link below, you will be logged into the se"
			."rver.\n\nIf you did not register this account, then please delete"
			." this email. Otherwise, please click the following URL to verify "
			."your email address with us. Thank you.\n\nhttp://$sitedomain/_s/"
			.$sesc,
			"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
		);
		if (1 || $page->vars['userlogin_send_admin_emails']) {
			$admins=dbAll(
				'select email from user_accounts,users_groups where groups_id=1 &'
				.'& user_accounts_id=user_accounts.id'
			);
			foreach ($admins as $admin) {
				mail(
					$admin['email'],
					'['.$sitedomain.'] user registration',
					"Hello!\n\nThis message is to alert you that a user ($email) ha"
					."s been created on your site, http://$sitedomain/ - the user h"
					."as not yet been activated, so please log into the admin area "
					."of the site (http://$sitedomain/ww.admin/ - under Site Option"
					."s then Users) and verify that the user details are correct.",
					"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
				);
			}
		}
		return userregistration_form(
			false,
			'<p><strong>Thank you for registering</strong>. Please check your e'
			.'mail for a verification URL. Once that\'s been followed, your acc'
			.'ount will be activated and a password supplied to you.</p>'
		);
	}
	else {
		$admins=dbAll(
			'select email from user_accounts,users_groups where groups_id=1 && '
			.'user_accounts_id=user_accounts.id'
		);
		foreach ($admins as $admin) {
			mail(
				$admin['email'],
				'['.$sitedomain.'] user registration',
				"Hello!\n\nThis message is to alert you that a user ($email) has "
				."been created on your site, http://$sitedomain/ - the user has n"
				."ot yet been activated, so please log into the admin area of the"
				." site (http://$sitedomain/ww.admin/ - under Site Options then U"
				."sers) and verify that the user details are correct.",
				"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain"
			);
		}
		return userregistration_form(
			false,
			'<p><strong>Thank you for registering</strong>. Our admins will mod'
			.'erate your registration, and you will receive an email when it is'
			.'activated.</p>'
		);
	}
	// }
}
function userregistration_showProfile() {
	$uid = addslashes($_SESSION['userdata'][ 'id' ]);
	$user = dbRow('select * from user_accounts where id=' . $uid);

	$phone = ($user[ 'phone' ] == '')
		?'<a href="javascript:edit_user_dialog(' . $user[ 'id' ] . ');">Add</a>'
		:htmlspecialchars($user[ 'phone' ]);

	// get array of groups the user is a member of
	$groups = array();
	$group_ids = dbAll(
		'select groups_id from users_groups where user_accounts_id=' . $uid
	);
	foreach ($group_ids as $key => $id) {
		array_push(
			$groups,
			dbOne('select name from groups where id=' . $id[ 'groups_id' ], 'name')
		);
	}
	$groups = implode(',', $groups);

	$html='<a class="logout" href="/?logout=1" style="float:right">Logout</a>
	<h2>' . htmlspecialchars($user[ 'name' ]) . '</h2>
	<div id="tabs">
		<ul>
			<li><a href="#details">User Details</a></li>
			<li><a href="#address">Address</a></li>
		</ul>
		<div id="details">

	<p style="float:right">
	<a href="javascript:edit_user_dialog('.$user['id'].');" id="edit-user-info">
		Edit Details
	</a>
	<a href="javascript:change_password_dialog(' . $user[ 'id' ] . ');"
	id="user-change-password" style="diplay:inline">Change Password</a></p>
	<table id="user-info" style="border:1px solid #ccc;margin:10px">
		<tr>
			<th>Email:</th><td>' . htmlspecialchars($user[ 'email' ]) . '</td>
		</tr>
		<tr>
			<th>Group(s):</th><td>' . htmlspecialchars($groups) . '</td>
		</tr>
		<tr>
			<th>Phone:</th><td>' . $phone . '</td>
		</tr>';

	$html .= '</table></div> <div id="address"><a id="new-address" href="java'
		.'script:add_address();" style="float:right">[+] Add Address</a> <div i'
		.'d="address-container"> <table> ';

	$addresses=json_decode($user['address'], true);
	foreach ($addresses as $name=>$address) {
	  $select=(@$address['default']=='yes')?' checked="checked"':'';
	  $html.=' <tr> <td> <input type="radio"'.$select
			.' name="default-address" value="'.$name.'"/> </td> <td>'
			.str_replace(' ', '-', $name).'</td> <td> <a href="javascript:edit_addr'
			.'ess(\''.$name.'\');" class="edit-addr" name="'.$name
			.'">[edit]</a> <a href="javascript:;" class="delete-addr" name="'
			.$name.'">[delete]</a> </td> </tr> ';
	}

	$html.='</table></div><br style="clear:both"/></div>
	</div>';

	$script = '
		function edit_user_dialog( id ){
			$( "<div id=\'users-dialog\' title=\'Edit User Details\'></div>" )
			.html( "Loading..." )
			.dialog({
				modal : true,
				buttons : {
					"Save" : function( ){
						var name = $( "input[name=\'user-name\']" ).val( );
						if( name == "" ){
							$( "#error" ).html( "the name field is required" );
							return false;
						}
						var phone = $( "input[name=\'user-phone\']" ).val( );
						$.post(
							"/ww.plugins/privacy/frontend/save_user_info.php",
							{ "name" : name, "phone" : phone }	
						);
						location.reload( true );
					},
					"Cancel" : function( ){
						$( "#users-dialog" ).dialog( "close" ).remove( );
					}
				}
			});
			$.get(
				"/ww.plugins/privacy/frontend/edit_user_info.php",
				function( html ){
					$( "#users-dialog" ).html( html );
				}
			);
		}
		function edit_address(id){
			$( "<div id=\'users-dialog\' title=\'Edit Address\'></div>" )
			.html( "Loading..." )
			.dialog({
				modal : true,
				buttons : {
					"Save" : function( ){
			  var name=$(\'input[name="add-name"]\').val();
			  var street=$(\'input[name="add-street"]\').val();
			  var street2=$(\'input[name="add-street2"]\').val();
			  var town=$(\'input[name="add-town"]\').val();
			  var county=$(\'input[name="add-county"]\').val();
			  var country=$(\'input[name="add-country"]\').val();
						$.post(
							"/ww.plugins/privacy/frontend/save_user_info.php?action=update",
							{
								"name" : name,
								"street" : street,
								"street2" : street2,
								"town" : town,
								"county" : county,
								"country" : country,
							}	
						);
						userdata.address[name]={
							"name" : name,
							"street" : street,
							"street2" : street2,
							"town" : town,
							"county" : county,
							"country" : country
						};
						$( "#users-dialog" ).dialog( "close" ).remove( );
					},
					"Cancel" : function( ){
						$( "#users-dialog" ).dialog( "close" ).remove( );
					}
				}
			});

			street=userdata.address[id].street;
			street2=userdata.address[id].street2;
			town=userdata.address[id].town;
			county=userdata.address[id].county;
			country=userdata.address[id].country;

			$("#users-dialog").html(
	  \'<table>\'
			+ \'<input type="hidden" name="add-name" value="\'+id+\'"/>\'
	  + \'<tr>\'
		+ \'<th>Street</th>\'
	 + \'<td><input type="text" name="add-street" value="\'+street+\'"/></td>\'
	  + \'</tr>\'
	  + \'<tr>\'
		+ \'<th>Street 2</th>\'
	+ \'<td><input type="text" name="add-street2" value="\'+street2+\'"/></td>\'
	  + \'</tr>\'
	  + \'<tr>\'
		+ \'<th>Town</th>\'
		+ \'<td><input type="text" name="add-town" value="\'+town+\'"/></td>\'
	  + \'</tr>\'
	  + \'<tr>\'
		+ \'<th>County</th>\'
	  + \'<td><input type="text" name="add-county" value="\'+county+\'"/></td>\'
	  + \'</tr>\'
	  + \'<tr>\'
		+ \'<th>Country</th>\'
	+ \'<td><input type="text" name="add-country" value="\'+country+\'"/></td>\'
	  + \'</tr>\'
	  + \'</table>\'
			);
		}
		$(function(){
			$("#tabs").tabs();
		});
		$(".delete-addr").live("click",function(){
			var name=$(this).attr("name");
			$(this).parent().parent().fadeOut("slow").remove();
			$.get(
				"/ww.plugins/privacy/frontend/save_user_info.php?action=delete"
				+"&address="+name
			);
		});
	';
	WW_addInlineScript($script);
	WW_addScript('/ww.plugins/privacy/frontend/change_password.js');
	$html .= plugin_trigger('privacy_user_profile', array($user));
	return $html;
}
function loginBox() {
	$page=Page::getInstanceByType(3);
	if (!$page) {
		return '<em>missing User Registration page</em>';
	}
	if (isset($_SESSION['userdata'])) {
		$c='<span class="login_info">Logged in as '
			.htmlspecialchars($_SESSION['userdata']['name'])
			.'. [<a href="?logout=1">logout</a>]</span>';
	}
	else{
		global $PAGEDATA;
		$c='<form class="login_box" action="'.$page->getRelativeUrl()
			.'" method="post"><table><tr><td><input name="email" value="Email" on'
			.'click="if(this.value==\'Email\')this.value=\'\'" /></td><td><input '
			.'name="password" type="password" /></td><td><input type="submit" nam'
			.'e="action" value="Login" /> or <a href="'.$page->getRelativeUrl()
			.'">register</a></td></tr></table><input type="hidden" name="login_re'
			.'ferer" value="'.$PAGEDATA->getRelativeUrl().'" /></form>';
	}
	return $c;
}

// if not logged in display login box
if (!isset($_SESSION[ 'userdata' ][ 'id' ])) {
	$html=userloginandregistrationDisplay();
	WW_addInlineScript('$(function(){$(".tabs").tabs()});');
}
else {
	$html = userregistration_showProfile();	
}
