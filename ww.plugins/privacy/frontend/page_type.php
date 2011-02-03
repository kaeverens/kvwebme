<?php
function userloginandregistrationDisplay(){
	// { variables
		$action=getVar('action');
		$c='';
		global $loggedin,$sitedomain,$DBVARS,$PAGEDATA;
	// }
	if (isset($_GET['hash']) && $_GET['hash'] && $_GET['email']) {
		$r=dbRow("select * from user_accounts where email='".addslashes($_GET['email'])."' and verification_hash='".addslashes($_GET['hash'])."'");
		if(!count($r))die('that hash and email combination does not exist');
		$password=Password::getNew();
		dbQuery("update user_accounts set password=md5('$password'),verification_hash='',active=1 where email='".addslashes($_GET['email'])."' and verification_hash='".addslashes($_GET['hash'])."'");
		mail($_GET['email'],'['.$sitedomain.'] user password created',"Your new password:\n\n".$password,"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain");
		$action='Login';
		$_REQUEST['email']=$_GET['email'];
		$_REQUEST['password']=$password;
	}
	if($action=='Login' || $loggedin){
		// { variables
			if($loggedin){
				$email=$_SESSION['userdata']['email'];
				$password=$_SESSION['userdata']['password'];
			}
			else{
				$email=$_REQUEST['email'];
				$password=$_REQUEST['password'];
			}
		// }
		$sql='select * from user_accounts where email="'.$email.'" and password=md5("'.$password.'") limit 1';
		$r=dbRow($sql);
		if($r){
			// { update session variables
				$loggedin=1;
				$r['password']=$password;
				$_SESSION['userdata']=$r;
			// }
			$n=$_SESSION['userdata']['name'];
			dbQuery('update user_accounts set last_view=now() where id='.$r['id']);
			if($action=='Login'){
				$redirect_url='';
				if(isset($_REQUEST['login_referer']) && strpos($_REQUEST['login_referer'],'/')===0){
					$redirect_url=$_REQUEST['login_referer'];
				}
				else if(isset($PAGEDATA->vars['userlogin_redirect_to'])
					&& $PAGEDATA->vars['userlogin_redirect_to']
				) {
					$p=Page::getInstance($PAGEDATA->vars['userlogin_redirect_to']);
					$redirect_url=$p->getRelativeUrl();
				}
				dbQuery('update user_accounts set last_login=now() where id='.$r['id']);
				if($redirect_url!='')redirect($redirect_url);
			}
			return userregistration_showProfile();
		}
		else unset($_SESSION['userdata']);
	}
	if($c=='')$c=$PAGEDATA->render();
	if($action=='Remind'){
		// { variables
			$email=getVar('email');
		// }
		$r=dbOne('select id from user_accounts where email="'.$email.'"','id');
		if($r){
			$p=Password::getNew();
			mail($email,'['.$sitedomain.'] user password changed',"Your new password:\n\n".$p,"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain");
			dbQuery('update user_accounts set password=md5("'.$p.'") where email="'.$email.'"');
			$c.='<script>$(document).ready(function(){$("<strong>Please check your email for your new password.</strong>").dialog({modal:true,height:100,width:150});});</script>';
		}else{
			$c.='<script>$(document).ready(function(){$("<strong>No user account with that email address exists.</strong>").dialog({modal:true,height:100,width:150});});</script>';
		}
	}
	if(!$PAGEDATA->vars['userlogin_visibility'])$PAGEDATA->vars['userlogin_visibility']=3;
	if(!$loggedin){ // show login and registration box
		$c.='<div class="tabs"><ul>';
		// { menu
		if($PAGEDATA->vars['userlogin_visibility']&1){
			$c.='<li><a href="#userLoginBoxDisplay">Login</a></li>';
			$c.='<li><a href="#userPasswordReminder">Password reminder</a></li>';
		}
		if($PAGEDATA->vars['userlogin_visibility']&2)$c.='<li><a href="#userregistration">Register</a></li>';
		// }
		$c.='</ul>';
		// { tabs
		if($PAGEDATA->vars['userlogin_visibility']&1){
			$c.= userLoginBoxDisplay();
			$c.=userPasswordReminder();
		}
		if($PAGEDATA->vars['userlogin_visibility']&2)$c.=userregistration();
		// }
		$c.='</div>';
	}
	return $c;
}
function userLoginBoxDisplay(){
	global $PAGEDATA;
	$c='<div id="userLoginBoxDisplay">';
	if(getVar('action')=='Login')$c.='<em>incorrect email or password given.</em>';
	if(isset($PAGEDATA->vars['userlogin_message_login']))$c.=$PAGEDATA->vars['userlogin_message_login'];
	$c.='<form class="userLoginBox" action="'.$GLOBALS['PAGEDATA']->getRelativeUrl().'#tab=Login" method="post"><table>';
	$c.='<tr><th><label for="email">Email</label></th><td><input type="text" name="email" value="'.getVar('email').'" /></td>';
	$c.='<th><label for="password">Password</label></th><td><input type="password" name="password" /></td></tr>';
	$c.='</table><input type="submit" name="action" value="Login" />';
	if(isset($_REQUEST['login_referer']))$c.='<input type="hidden" name="login_referer" value="'.htmlspecialchars($_REQUEST['login_referer'],ENT_QUOTES).'" />';
	$c.='</form>';
	$c.='</div>';
	return $c;
}
function userPasswordReminder(){
	global $PAGEDATA;
	$c='<div id="userPasswordReminder">';
	if(isset($PAGEDATA->vars['userlogin_message_reminder']))$c.=$PAGEDATA->vars['userlogin_message_reminder'];
	$c.='<form class="userLoginBox" action="'.$GLOBALS['PAGEDATA']->getRelativeUrl().'#tab=Password Reminder" method="post"><table>';
	$c.='<tr><th><label for="email">Email</label></th><td><input type="text" name="email" /></td></tr></table>';
	$c.='<input type="submit" name="action" value="Remind" /></form>';
	$c.='</div>';
	return $c;
}
function userregistration(){
	if(getVar('a')=='Register')return userregistration_register();
	return userregistration_form();
}
function userregistration_form($error='',$alert=''){
	global $PAGEDATA;
	$c='<div id="userregistration">';
	if(isset($PAGEDATA->vars['userlogin_message_registration']))$c.=$PAGEDATA->vars['userlogin_message_registration'];
	$c.=$error.'<form class="userRegistrationBox" action="'.$GLOBALS['PAGEDATA']->getRelativeUrl().'#userregistration" method="post"><table>'
		.'<tr><th>Name</th><td><input type="text" name="name" value="'.htmlspecialchars(getVar('name')).'" /></td>'
		.'<th>Email</th><td><input type="text" name="email" value="'.htmlspecialchars(getVar('email')).'" /></td></tr></table>';
	if(isset($PAGEDATA->vars['privacy_extra_fields']) && strlen($PAGEDATA->vars['privacy_extra_fields'])>2){
		$c.='<table>';
		$required=array();
		$rs=json_decode($PAGEDATA->vars['privacy_extra_fields']);
		$cnt=0;
		foreach($rs as $r){
			if (!$r->name || $r->type=='hidden') {
				continue;
			}
			$name=preg_replace('/[^a-zA-Z0-9_]/','',$r->name);
			$class='';
			if (isset($r->is_required) && $r->is_required) {
				$required[]=$name.','.$r->type;
				$class=' required';
			}
			if (isset($_REQUEST[$name])) {
				$_SESSION['privacys'][$name]=$_REQUEST[$name];
			}
			$val=getVar($name);
			if(!$val && isset($_SESSION['userdata']) && $_SESSION['userdata']){
				switch($name){
					case 'Email': case '__ezine_subscribe': // {
						$val=$_SESSION['userdata']['email'];
						break;
					// }
					case 'FirstName': // {
						$val=preg_replace('/ .*/','',$_SESSION['userdata']['name']);
						break;
					// }
					case 'Street': // {
						$val=$_SESSION['userdata']['address1'];
						break;
					// }
					case 'Street2': // {
						$val=$_SESSION['userdata']['address2'];
						break;
					// }
					case 'Surname': // {
						$val=preg_replace('/.* /','',$_SESSION['userdata']['name']);
						break;
					// }
					case 'Town': // {
						$val=$_SESSION['userdata']['address3'];
						break;
					// }
				}
			}
			if(!isset($_REQUEST[$name]))$_REQUEST[$name]='';
			switch($r->type){
				case 'checkbox': {
					$d='<input type="checkbox" id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'"';
					if($_REQUEST[$name])$d.=' checked="'.$_REQUEST[$name].'"';
					$d.=' class="'.$class.' checkbox" />';
					break;
				}
				case 'ccdate': {
					if($_REQUEST[$name]=='')$_REQUEST[$name]=date('Y-m');
					$d='<input name="privacy_extras_'.$name.'" value="'.$_REQUEST[$name].'" class="ccdate" />';
					break;
				}
				case 'date': {
					if($_REQUEST[$name]=='')$_REQUEST[$name]=date('Y-m-d');
					$d='<input name="privacy_extras_'.$name.'" value="'.$_REQUEST[$name].'" class="date" />';
					break;
				}
				case 'email':{
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'" value="'.$val.'" class="email'.$class.' text" />';
					break;
				}
				case 'file': {
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'" type="file" />';
					break;
				}
				case 'hidden': {
					$d='<textarea id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'" class="'.$class.' hidden">'.htmlspecialchars($r->extra).'</textarea>';
					break;
				}
				case 'selectbox': {
					$d='<select id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'">';
					$arr=explode("\n",htmlspecialchars($r->extra));
					foreach($arr as $li){
						if($_REQUEST[$name]==$li)$d.='<option selected="selected">'.rtrim($li).'</option>';
						else $d.='<option>'.rtrim($li).'</option>';
					}
					$d.='</select>';
					break;
				}
				case 'textarea': {
					$d='<textarea id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'" class="'.$class.'">'.$_REQUEST[$name].'</textarea>';
					break;
				}
				default:{ # input boxes, and anything which was not handled already
					$d='<input id="privacy_extras_'.$name.'" name="privacy_extras_'.$name.'" value="'.$val.'" class="'.$class.' text" />';
					break;
				}
			}
			$c.='<tr><th>'.htmlspecialchars(__($r->name));
			if (isset($r->is_required) && $r->is_required) {
				$c.='<sup>*</sup>';
			}
			$c.="</th>\n\t<td>".$d."</td></tr>\n\n";
			$cnt++;
		}
		$c.='</table>';
		if(count($required))$c.='<br />'.__('* indicates required fields');
	}
	if(isset($PAGEDATA->vars['userlogin_terms_and_conditions']) && $PAGEDATA->vars['userlogin_terms_and_conditions']){
		$c.='<input type="checkbox" name="terms_and_conditions" /> I agree to the <a href="javascript:userlogin_t_and_c()">terms and conditions</a>.<br />';
		$c.='<script>function userlogin_t_and_c(){$("<div>'.addslashes(str_replace(array("\n","\r"),' ',$PAGEDATA->vars['userlogin_terms_and_conditions'])).'</div>").dialog({modal:true,width:"90%"});}</script>';
	}
	if ($alert) {
		WW_addInlineScript('$(function(){$(\'<div>'.addslashes($alert).'</div>\').dialog({modal:true});});');
	}
	$c.='<input type="submit" name="a" value="Register" />'
		.'</form></div>';
	return $c;
}
function userregistration_register(){
	global $DBVARS,$PAGEDATA;
	// { variables
		$name=getVar('name');
		$email=getVar('email');
		$phone=getVar('phone');
		$usertype=getVar('usertype');
		$address1=getVar('address1');
		$address2=getVar('address2');
		$address3=getVar('address3');
		$howyouheard=getVar('howyouheard');
	// }
	if(isset($PAGEDATA->vars['userlogin_terms_and_conditions']) && $PAGEDATA->vars['userlogin_terms_and_conditions'] && !isset($_REQUEST['terms_and_conditions']))return '<em>You must agree to the terms and conditions. Please press "Back" and try again.</em>';
	// { check for user_account table "extras"
		$extras=array();
		$rs=json_decode($PAGEDATA->vars['privacy_extra_fields']);
		foreach($rs as $r){
			if(!$r->name)continue;
			$ename=preg_replace('/[^a-zA-Z0-9_]/','',$r->name);
			$extras[$r->name]=isset($_REQUEST['privacy_extras_'.$ename])
				?$_REQUEST['privacy_extras_'.$ename]
				:'';
		}
	// }
	// { check for required fields
	$missing=array();
	if (!$name) {
		$missing[]='your name';
	}
	if (!$email) {
		$missing[]='your email address';
	}
	foreach ($rs as $r) {
		if (isset($r->is_required) && $r->is_required && (!isset($_REQUEST['privacy_extras_'.$r->name]) || !$_REQUEST['privacy_extras_'.$r->name])) {
			$missing[]=$r->name;
		}
	}
	if(count($missing)) {
		return userregistration_form('<em>You must fill in the following fields: '.join(', ', $missing).'</em>');
	}
	// }
	// { check if the email address is already registered
		$r=dbRow('select id from user_accounts where email="'.$email.'"');
		if($r && count($r))return userregistration_form('<p><em>That email is already registered.</em></p>');
	// }
	// { register the account
		$password=Password::getNew();
		$r=dbRow("SELECT * FROM site_vars WHERE name='user_discount'");
		$discount=(float)$r['value'];
		$hash=base64_encode(sha1(rand(0,65000),true));
		$sql='insert into user_accounts set name="'.$name.'", password=md5("'.$password.'"), email="'.$email.'", verification_hash="'.$hash.'", active=0, extras="'.addslashes(json_encode($extras)).'"';
		dbQuery($sql);
		$page=$GLOBALS['PAGEDATA'];
		$id=dbOne('select last_insert_id() as id','id');
		if(isset($page->vars['userlogin_groups'])){
			$gs=json_decode($page->vars['userlogin_groups'],true);
			foreach($gs as $k=>$v){
				dbQuery("insert into users_groups set user_accounts_id=$id,groups_id=".(int)$k);
			}
		}
		$sitedomain=$_SERVER['HTTP_HOST'];
		$long_url="http://$sitedomain".$page->getRelativeUrl()."?hash=".urlencode($hash)."&email=".urlencode($email).'#Login';
		$short_url=md5($long_url);
		$lesc=addslashes($long_url);
		$sesc=urlencode($short_url);
		dbQuery("insert into short_urls values(0,now(),'$lesc','$short_url')");
		if($page->vars['userlogin_registration_type']=='Email-verified'){
    	mail($email,'['.$sitedomain.'] user registration',"Hello!\n\nThis message is to verify your email address, which has been used to register a user-account on the $sitedomain website.\n\nAfter clicking the link below, you will be logged into the server, and a new password will be emailed out to you.\n\nIf you did not register this account, then please delete this email. Otherwise, please click the following URL to verify your email address with us. Thank you.\n\nhttp://$sitedomain/_s/".$sesc,"From: noreply@$sitedomain\nReply-to: noreply@$sitedomain");
			if(1 || $page->vars['userlogin_send_admin_emails']){
				$admins=dbAll('select email from user_accounts,users_groups where groups_id=1 && user_accounts_id=user_accounts.id');
				foreach($admins as $admin){
					mail($admin['email'],'['.$sitedomain.'] user registration',"Hello!\n\nThis message is to alert you that a user has been created on your site, http://$sitedomain/ - the user has not yet been activated, so please log into the admin area of the site (http://$sitedomain/ww.admin/ - under Site Options then Users) and verify that the user details are correct.","From: noreply@$sitedomain\nReply-to: noreply@$sitedomain");
				}
			}
			return userregistration_form(false,'<p><strong>Thank you for registering</strong>. Please check your email for a verification URL. Once that\'s been followed, your account will be activated and a password supplied to you.</p>');
		}
		else{
			$admins=dbAll('select email from user_accounts,users_groups where groups_id=1 && user_accounts_id=user_accounts.id');
			foreach($admins as $admin){
				mail($admin['email'],'['.$sitedomain.'] user registration',"Hello!\n\nThis message is to alert you that a user ($email) has been created on your site, http://$sitedomain/ - the user has not yet been activated, so please log into the admin area of the site (http://$sitedomain/ww.admin/ - under Site Options then Users) and verify that the user details are correct.","From: noreply@$sitedomain\nReply-to: noreply@$sitedomain");
			}
			return userregistration_form(false,'<p><strong>Thank you for registering</strong>. Our admins will moderate your registration, and you will receive an email with your new password when it is activated.</p>');
		}
	// }
}
function userregistration_showProfile(){
	$ud=$_SESSION['userdata'];
	$name=$ud['name']?$ud['name']:'';
	$c='<a class="logout" href="/?logout=1">log out</a><h2>User Profile: '.htmlspecialchars($name).'</h2><table>';
	$c.='</table>';
	return $c;
}
function loginBox(){
	$page=Page::getInstanceByType(3);
	if(!$page)return '<em>missing User Registration page</em>';
	global $PAGEDATA;
	if(isset($_SESSION['userdata'])){
		$c='<span class="login_info">Logged in as '.htmlspecialchars($_SESSION['userdata']['name']).'. [<a href="?logout=1">logout</a>]</span>';
	}
	else{
		$c='<form class="login_box" action="'.$page->getRelativeUrl().'" method="post"><table><tr><td><input name="email" value="Email" onclick="if(this.value==\'Email\')this.value=\'\'" /></td><td><input name="password" type="password" /></td><td><input type="submit" name="action" value="Login" /> or <a href="'.$page->getRelativeUrl().'">register</a></td></tr></table><input type="hidden" name="login_referer" value="'.$PAGEDATA->getRelativeUrl().'" /></form>';
	}
	return $c;
}
$html=userloginandregistrationDisplay();
WW_addInlineScript('$(function(){$(".tabs").tabs()});');
