<?php
/**
	* login form for admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo "<!doctype html>\n";
echo '<html><head><script src="https://ajax.googleapis.com/ajax/libs/jquery/'
	.'1.8/jquery.min.js"></script>'
	.'<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/'
	.'jquery-ui.min.js"> </script> <script src="/j/lang.js"></script>'
	.'<link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/'
	.'base/jquery-ui.css" rel="stylesheet"/>'
	.'<link href="/ww.admin/theme/admin.css" rel="stylesheet"/> </head> <body>'
	.'<div id="tabs" style="width:350px;margin:0 auto;"> <ul>'
	.'<li><a href="#admin-login">'.__('Login').'</a></li>'
	.'<li><a href="#admin-forgotten-password">'.__('Forgotten Password').'</a>'
	.'</li></ul> <form id="admin-login" action="./" method="post"> <table>'
	.'<tr><th>'.__('Email').'</th><td><input id="email" type="email" /></td></tr>'
	.'<tr><th>'.__('Password').'</th><td><input type="password" id="password"/>'
	.'</td> </tr>'
	.'<tr><th colspan="2" style="text-align:right;"><button id="login">'
	.__('Log In').'</button> </th> </tr></table> </form>'
	.'<form id="admin-forgotten-password" action="./" method="post"> <table>'
	.'<tr><th>'.__('Email').'</th><td><input id="email-r" type="email" /></td>'
	.'</tr>'
	.'<tr><th colspan="2" style="text-align:right;"><button id="send-token">'
	.__('Send Token').'</button> </th> </tr></table><p>'.__(
		'If you\'ve forgotten your password, use the form above to send a token'
		.' for creating a new password, then use the token below to change your'
		.' password'
	)
	.'</p><table>'
	.'<tr><th>'.__('Token').'</th><td><input id="token"/></td></tr>'
	.'<tr><th>'.__('New Password').'</th><td>'
	.'<input id="password2" type="password"/></td> </tr>'
	.'<tr><th>'.__('Repeat Password').'</th><td>'
	.'<input id="password3" type="password"/></td> </tr>'
	.'<tr><th colspan="2" style="text-align:right;"><button id="change-password">'
	.__('Change Password').'</button> </th></tr>'
	.'</table> </form> </div> <script defer="defer"> $(function(){'
	.'$("#tabs").tabs().find("#email").focus();'
	.'$("#admin-login").submit(function(){$("#login").click();return false;});'
	.'$("#login").click(function(){ var email=$("#email").val(),'
	.'pass=$("#password").val(); if (email=="" || pass=="") {'
	.'return alert("'.__('Both email and password must be filled in').'"); }'
	.'$.post("/a/f=login", { "email":   email, "password":pass }, function(ret) {'
	.'if (ret.error) { return alert(ret.error); }'
	.'document.location=document.location; }); return false; });'
	.'$("#admin-forgotten-password").submit(function() {'
	.'$("#send-token").click(); return false; });'
	.'$("#send-token").click(function(){ var email=$("#email-r").val();'
	.'if (email=="") { return alert("'.__('The email must be filled in').'"); }'
	.'$.post("/a/f=sendLoginToken", { "email":   email }, function(ret) {'
	.'if (ret.error) { return alert(ret.error); } alert("'
	.__('Please check your email, then use the form below to reset your password')
	.'"); }); return false; }); $("#change-password").click(function(){'
	.'var email=$("#email-r").val(), token=$("#token").val(),'
	.'pass2=$("#password2").val(), pass3=$("#password3").val();'
	.'if (email=="" || token=="" || pass2=="" || pass3=="") {'
	.'return alert("'.__('Email, token and passwords must be filled in').'"); }'
	.'if (pass2!=pass3) { return alert("'.__('Passwords do not match').'"); }'
	.'$.post("/a/f=updateUserPasswordUsingToken", { "email":   email,'
	.'"password":pass2, "token":   token }, function(ret) { if (ret.error) {'
	.'return alert(ret.error); } $("#email").val(email);'
	.'$("#password").val(pass2);alert("'
	.__('Password updated. you can log in now').'"); $("#login").click(); });'
	.'return false; }); });'
	.'</script> </body> </html>';
