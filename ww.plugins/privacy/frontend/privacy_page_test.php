<?php

// assume no access
$allowed=false;

// if there's no restriction on this page, then $allowed=true
if((!isset($pagedata->vars['restrict_to_groups']) || $pagedata->vars['restrict_to_groups']=='')
	&& (!isset($pagedata->vars['privacy_password']) || $pagedata->vars['privacy_password']=='')
) {
	$allowed=true;
	return;
}

// if the user is logged in and a member of a group with access, then return true
if(isset($_SESSION['userdata']['groups'])
	&& count($_SESSION['userdata']['groups'])
) {
	$gs=json_decode($pagedata->vars['restrict_to_groups']);
	foreach ($_SESSION['userdata']['groups'] as $k=>$id) {
		if (isset($gs->$id)) {
			$allowed=true;
			return;
		}
	}
}

// check if a password is set
if (isset($pagedata->vars['privacy_password']) && $pagedata->vars['privacy_password']!='') {
	$guess='';
	if (isset($_SESSION['privacy_password_'.$pagedata->id])) {
		$guess=$_SESSION['privacy_password_'.$pagedata->id];
	}
	if (isset($_REQUEST['privacy_password'])) {
		$guess=$_REQUEST['privacy_password'];
	}
	if ($pagedata->vars['privacy_password'] == $guess) {
		$_SESSION['privacy_password_'.$pagedata->id]=$guess;
		$allowed=true;
		return;
	}
}
