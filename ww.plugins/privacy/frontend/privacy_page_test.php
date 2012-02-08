<?php
/**
	* check to see if the page is restricted
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/


// assume no access
$allowed=false;

// if user is not logged in and "privacy_require_login" is set, return false
if (!isset($_SESSION['userdata'])
	&& @$pagedata->vars['privacy_require_login']
) {
	$allowed=false;
	return;
}

// if there's no restriction on this page, then return true
if (!@$pagedata->vars['restrict_to_groups']
	&& !@$pagedata->vars['privacy_password']
) {
	$allowed=true;
	return;
}

// if the user is logged in and a member of a group with access, return true
if (isset($_SESSION['userdata']['groups'])
	&& count($_SESSION['userdata']['groups'])
) {
	if (@$pagedata->vars['restrict_to_groups']!='') {
		$gs=json_decode($pagedata->vars['restrict_to_groups']);
		foreach ($_SESSION['userdata']['groups'] as $k=>$id) {
			if (isset($gs->$id)) {
				$allowed=true;
				return;
			}
		}
	}
}

// check if a password is set
if (isset($pagedata->vars['privacy_password'])
	&& $pagedata->vars['privacy_password']!=''
) {
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
