<?php
if(!defined('START_TIME'))define('START_TIME',microtime(true));
$ignore_cms_plugins=true;
include_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$GLOBALS['kfm_userfiles_address']=$GLOBALS['DBVARS']['userbase'].'/f/';
if(!session_id()){
	if(isset($_GET['cms_session']))session_id($_GET['cms_session']);
	session_start();
}
if($_SERVER['PHP_SELF']!='/j/kfm/get.php' && (!isset($GLOBALS['kfm_api_auth_override'])||!$GLOBALS['kfm_api_auth_override']) && !Core_isAdmin()){
	echo 'access denied!';
	exit;
}
if($_SERVER['PHP_SELF']=='/j/kfm/get.php'){
	$GLOBALS['kfm_do_not_save_session']=true;
}
$GLOBALS['kfm_api_auth_override']=true;
$GLOBALS['kfm']->defaultSetting('theme', 'default');
$GLOBALS['kfm']->defaultSetting('file_handler','return');
$GLOBALS['kfm']->defaultSetting('file_url','filename');
$GLOBALS['kfm']->defaultSetting('return_file_id_to_cms',$GLOBALS['kfm_return_file_id_to_cms']);
