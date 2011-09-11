<!doctype html>
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		<script src="/j/lang.js"></script>
		<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet"/>
		<link href="/ww.admin/theme/admin.css" rel="stylesheet"/>
	</head>
	<body>
		<div id="tabs" style="width:350px;margin:0 auto;">
			<ul>
				<li><a href="#admin-login">Login</a></li>
				<li><a href="#admin-forgotten-password">Forgotten Password</a></li>
			</ul>
			<div id="admin-login">
				<table>
					<tr>
						<th class="__">Email</th>
						<td><input id="email" type="email" /></td>
					</tr>
					<tr>
						<th class="__">Password</th>
						<td><input type="password" id="password" /></td>
					</tr>
					<tr>
						<th colspan="2" style="text-align:right;">
							<button id="login" class="__">Log In</button>
						</th>
					</tr>
				</table>
			</div>
			<div id="admin-forgotten-password">
				<table>
					<tr>
						<th class="__">Email</th>
						<td><input id="email-r" type="email" /></td>
					</tr>
					<tr>
						<th colspan="2" style="text-align:right;">
							<button id="send-token" class="__">Send Token</button>
						</th>
					</tr>
				</table>
				<p class="__">If you've forgotten your password, use the form above to send a token for creating a new password, then use the token below to change your password</p>
				<table>
					<tr><th class="__">Token</th><td><input id="token"/></td></tr>
					<tr><th class="__">New Password</th><td><input id="password2" type="password"/></td></tr>
					<tr><th class="__">(repeat)</th><td><input id="password3" type="password"/></td></tr>
					<tr><th colspan="2" style="text-align:right;"><button id="change-password" class="__">Change Password</button></th></tr>
				</table>
			</div>
		</div>
		<script>
			$(function(){
				$('#tabs').tabs().find('#email').focus();
				$('#login').click(function(){
					var email=$('#email').val(), pass=$('#password').val();
					if (email=='' || pass=='') {
						return alert('<?php echo __('both email and password must be filled in'); ?>');
					}
					$.post('/a/f=login', {
						'email':   email,
						'password':pass
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						document.location=document.location;
					});
				});
				$('#send-token').click(function(){
					var email=$('#email-r').val();
					if (email=='') {
						return alert('<?php echo __('the email must be filled in'); ?>');
					}
					$.post('/a/f=sendLoginToken', {
						'email':   email
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						alert('<?php echo __('please check your email, then use the form below to reset your password'); ?>');
					});
				});
				$('#change-password').click(function(){
					var email=$('#email-r').val(), token=$('#token').val(),
						pass2=$('#password2').val(), pass3=$('#password3').val();
					if (email=='' || token=='' || pass2=='' || pass3=='') {
						return alert('<?php echo __('email, token and passwords must be filled in'); ?>');
					}
					if (pass2!=pass3) {
						return alert('<?php echo __('passwords do not match'); ?>');
					}
					$.post('/a/f=updateUserPasswordUsingToken', {
						'email':   email,
						'password':pass2,
						'token':   token
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						$('#email').val(email);
						$('#password').val(pass2);
						alert('<?php echo __('password updated. you can log in now.'); ?>');
						$('#login').click();
					});
				});
			});
		</script>
	</body>
</html>
