<?php
$existing_accounts=dbOne('select count(id) as ids from user_accounts','ids');
if(isset($_REQUEST['action']) && $_REQUEST['action']==__('remind')){
	$email=$_REQUEST['email'];
	if(filter_var($email,FILTER_VALIDATE_EMAIL)){
		$u=dbRow("SELECT * FROM user_accounts WHERE email='$email'");
		if(count($u)){
			$passwd=Password::getNew();
			dbQuery("UPDATE user_accounts SET password=md5('$passwd') WHERE email='$email'");
			mail($email,'['.$sitedomain.'] admin password reset','Your new password is "'.$passwd.'". Please log into the admin area and change it to something else.',"Reply-to: $email\nFrom: $email");
		}
	}
}
if(!$existing_accounts && isset($_REQUEST['email']) && isset($_REQUEST['password'])){
	$email=$_REQUEST['email'];
	$password=md5($_REQUEST['password']);
	if(!filter_var($email,FILTER_VALIDATE_EMAIL))$message=__('Please make sure to use a valid email address');
	else{
		dbQuery("insert into user_accounts (id,email,name,password,active,parent) values(1,'".addslashes($email)."','Administrator','$password',1,0)");
		dbQuery("insert into groups values(1,'administrators',0)");
		dbQuery("insert into users_groups values(1,1)");
		$message='User account created. Please login now (press F5 and choose to resubmit the login data)';
	}
}
?>
<html>
	<head>
		<title><?php echo __('Login'); ?></title>
		<link rel="stylesheet" type="text/css" href="/ww.admin/theme/login.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.6/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/js/"></script>
		<script>
			$(function() {
				$('#login-tabs').tabs();
			});
		</script>
	</head>
 <body onload="document.getElementById('email').focus();">
 	<div id="wrapper">
	
	<div id="header"><div id="topImage"></div></div>
	
	<div id="mainContent">
	<div class="paragraph">
		<p>
<?php
if(!$existing_accounts){
	echo '<em><strong>No user accounts exist yet</strong>. Please log in using your email address and a password of your choice. This will become the first admin user account.</em>';
}
else echo __('To access the administrative features of your website, you will need to enter the username and password below and click "login".');
if(isset($message) && $message!='')echo '<br /><br /><strong>'.$message.'</strong>';
?>
		</p>
	</div>
	<div id="login-tabs" style="width:40%;margin:0 auto;">
		<ul>
			<li><a href="#admin-login">Login</a></li>
			<li><a href="#admin-reminder">Reminder</a></li>
		</ul>
		<div id="admin-login">
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<table cols="3">
			   	<tr><th colspan="1"><?php echo __('email'); ?></th><td colspan="2"><input id="email" name="email" /></td></tr>
			   	<tr><th colspan="1"><?php echo __('password'); ?></th><td colspan="2"><input type="password" name="password" /></td></tr>
				<tr><th colspan="3" align="right"><input name="action" type="submit" value="<?php echo __('login'); ?>" class="login" /></th></tr>
				</table>
	   	</form>
		</div>
		<div id="admin-reminder">
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<table cols="3">
			   	<tr><th colspan="1"><?php echo __('email'); ?></th><td colspan="2"><input id="email" type="text" name="email" /></td></tr>
					<tr><th colspan="3" align="right"><input name="action" type="submit" value="<?php echo __('remind'); ?>" class="login" /></th></tr>
				</table>
				<p><?php echo __('Use this form to create a new password for yourself.'); ?></p>
	   	</form>
		</div>
	</div>
	</div>
 </body>
</html>
