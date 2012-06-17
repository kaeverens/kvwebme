<?php
/**
	* admin page for user registration/login
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$html='<tr><td colspan="6"><div class="tabs">'
	.'<ul>'
	.'<li><a href="#privacy-header">Header</a></li>'
	.'<li><a href="#privacy-options">Options</a></li>'
	.'<li><a href="#privacy-messages">Messages</a></li>'
	.'<li><a href="#privacy-conditions">Terms and Conditions</a></li>'
	.'<li><a href="#privacy-data">Extra User Data</a></li>'
	.'</ul>';
// { main
$html.='<div id="privacy-header">';
$html.='<p>This will appear above the login/registration form</p>';
$html.=ckeditor(
	'body',
	$page['body'],
	false
);
$html.='</div>';
// }
// { options
$html.='<div id="privacy-options"><table style="width:100%">';
// { visibility, user groups
if (!isset($page_vars['userlogin_visibility'])) {
	$page_vars['userlogin_visibility']=3;
}
$html.='<tr><th>Visibility</th><td>'
	.'<select name="page_vars[userlogin_visibility]">';
$arr=array(
	'3'=>'Login and Register forms',
	'1'=>'Login form',
	'2'=>'Register form'
);
foreach ($arr as $k=>$v) {
	$html.='<option value="'.$k.'"';
	if ($k==$page_vars['userlogin_visibility']) {
		$html.=' selected="selected"';
	}
	$html.='>'.htmlspecialchars($v).'</option>';
}
$html.='</select></td>';
$html.='<th rowspan="3">Add New Users To</th><td rowspan="3">';
$groups=array();
$grs=dbAll('select id,name from groups');
$gms=array();
$gms='{}';
if (isset($page_vars['userlogin_groups'])) {
	$gms=$page_vars['userlogin_groups'];
}
$gms=json_decode($gms);
foreach ($grs as $g) {
	$groups[$g['id']]=$g['name'];
}
foreach ($groups as $k=>$g) {
	$html.='<input type="checkbox" name="page_vars[userlogin_groups]['.$k.']"';
	if (isset($gms->$k)) {
		$html.=' checked="checked"';
	}
	$html.=' />'.htmlspecialchars($g).'<br />';
}
$html.='</td></tr>';
// }
// { registration type
$html.='<tr><th>Registration type:</th><td>';
$html.='<select name="page_vars[userlogin_registration_type]">';
$html.='<option>Moderated</option>';
$html.='<option';
$emailVerified=@$page_vars['userlogin_registration_type']=='Email-verified';
if ($emailVerified) {
	$html.=' selected="selected"';
}
$html.='>Email-verified</option>';
$html.='</select></td></tr>';
// }
// { redirect on login
$html.='<tr><th>Redirect on login:</th><td>';
$html.='<select id="page_vars_userlogin_redirect_to" name="'
	.'page_vars[userlogin_redirect_to]">';
if (@$page_vars['userlogin_redirect_to']) {
	$parent=Page::getInstance($page_vars['userlogin_redirect_to']);
	$html.='<option value="'.$parent->id.'">'
		.htmlspecialchars(__FromJSON($parent->name))
		.'</option>';
}
else {
	$page_vars['userlogin_redirect_to']=0;
	$html.='<option value="0"> -- none -- </option>';
}
$html.='</select></td></tr>';
// }
$html.='</table></div>';
// }
// { messages
$html.='<div id="privacy-messages"><div class="tabs">';
$html.='<ul>';
$html.='<li><a href="#privacy-messages-login">Login</a></li>';
$html.='<li><a href="#privacy-messages-reminder">Reminder</a></li>';
$html.='<li><a href="#privacy-messages-registeration">Registeration</a></li>';
$html.='</ul>';
// { Login header
$html.='<div id="privacy-messages-login"><br />';
$html.='<p>This message appears above the login form.</p>';
if (!isset($page_vars['userlogin_message_login'])) {
	$page_vars['userlogin_message_login']='<p>'.__(
		'Please log in using your email address and password.'
		.' If you don\'t already have a user account, please use the Register'
		.' tab (see above) to register.',
		'core'
	)
		.'</p>';
}
$html.=ckeditor(
	'page_vars[userlogin_message_login]',
	$page_vars['userlogin_message_login'],
	false
);
$html.='</div>';
// }
// { Reminder header
$html.='<div id="privacy-messages-reminder"><br />';
$html.='<p>This message appears above the password reminder form.</p>';
if (!isset($page_vars['userlogin_message_reminder'])) {
	$page_vars['userlogin_message_reminder']='<p>'.__(
		'If you have forgotten your password, please enter your email address'
		.' here to have a new verification email sent out to you.', 'core'
	)
		.'</p>';
}
$html.=ckeditor(
	'page_vars[userlogin_message_reminder]',
	$page_vars['userlogin_message_reminder'],
	false
);
$html.='</div>';
// }
// { Register header
$html.='<div id="privacy-messages-registeration"><br />';
$html.='<p>This message appears above the user registration form.</p>';
if (!isset($page_vars['userlogin_message_registration'])) {
	$page_vars['userlogin_message_registration']='<p>'.__(
		'Please enter your name and email address. After submitting, please'
		.' check your email account for your account verification link.', 'core'
	)
		.'</p>';
}
$html.=ckeditor(
	'page_vars[userlogin_message_registration]',
	$page_vars['userlogin_message_registration'],
	false
);
$html.='</div>';
// }
$html.='</div></div>';
// }
// { terms and conditions
if (!isset($page_vars['userlogin_terms_and_conditions'])) {
	$page_vars['userlogin_terms_and_conditions']='';
}
$contents = $page_vars['userlogin_terms_and_conditions'];
$html.='<div id="privacy-conditions">'
	.'<p>Leave blank if no terms and conditions agreement is needed</p>'
	.ckeditor('page_vars[userlogin_terms_and_conditions]', $contents, false)
	.'</div>';
// }
// { addition privacy fields
$html.= '<div id="privacy-data">';
$html.='<p>These are fields that you can ask your subscribers to fill-in '
	.'for your info.</p>';
$html.= '<table id="privacyfieldsTable" width="100%"><tr>';
$html.='<th width="30%">Name</th>';
$html.='<th width="30%">Type</th>';
$html.='<th width="10%">Required</th>';
$html.='<th id="extrasColumn">'
	.'<a href="javascript:privacyfieldsAddRow()">add field</a></th></tr>';
$html.='</table>';
$html.='<ul id="privacy_fields" style="list-style:none">';
if (!isset($page_vars['privacy_extra_fields'])) {
	$page_vars['privacy_extra_fields']='[]';
}
$rs=json_decode($page_vars['privacy_extra_fields']);
$i=0;
$arr
	=array(
		'email'=>'email',
		'url'=>'url',
		'input box'=>'input box',
		'textarea'=>'textarea',
		'date'=>'date',
		'checkbox'=>'checkbox',
		'selectbox'=>'selectbox',
		'hidden'=>'hidden message',
		'ccdate'=>'credit card expiry date'
);
foreach ($rs as $r) {
	if (!isset($r->name)) {
		continue;
	}
	if (!isset($r->type)) {
		$r->type='input box';
	}
	if (!isset($r->validation)) {
		$r->validation='';
	}
	if (!isset($r->is_required)) {
		$r->is_required=false;
	}
	if (!isset($r->extra)) {
		$r->extra='';
	}
	$html.= '<li><table width="100%"><tr><td width="30%"><input name="'
		.'page_vars[privacy_extra_fields]['.$i.'][name]" value="'
		.htmlspecialchars($r->name).'"/></td><td width="30%"><select name="'
		.'page_vars[privacy_extra_fields]['.$i.'][type]">';
	foreach ($arr as $k=>$v) {
		$html.='<option value="'.htmlspecialchars($k).'"';
		if ($k==$r->type) {
			$html.=' selected="selected"';
		}
		$html.='>'.htmlspecialchars($v).'</option>';
	}
	$html.='</select></td><td width="10%"><input type="checkbox" name="'
		.'page_vars[privacy_extra_fields]['.$i.'][is_required]"';
	if ($r->is_required) {
		$html.=' checked="checked"';
	}
	$html.='</td><td>';
	switch($r->type){
		case 'selectbox':case 'hidden':{
			$html.='<textarea name="page_vars[privacy_extra_fields]['
				.($i++).'][extra]" class="small">'.htmlspecialchars($r->extra)
				.'</textarea>';
			break;
		}
		default:{
			$html.='<input type="hidden" name="page_vars[privacy_extra_fields]['
				.($i++).'][extra]" value="'.htmlspecialchars($r->extra)
				.'"/>';
		}
	}
	$html.= '</td></tr></table></li>';
}
$html.='</ul>';
$html.='<script>var privacyfieldElements='.$i.';</script>';
$html.='<script src="/ww.plugins/privacy/j/admin.fields.js"></script>';
$html.='</div>';
// }
$html.='</div><script>var page_vars_userlogin_redirect_to='
	.$page_vars['userlogin_redirect_to']
	.';
$(function(){
	$("#page_vars_userlogin_redirect_to")
		.remoteselectoptions({url:"/a/f=adminPageParentsList"});
});</script>';
$html.='</td></tr>';
