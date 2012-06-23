<?php
/**
	* API front controller
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'basics.php';

// { extract parameters from URL
$remainder='';
if ($_REQUEST['extra']!='') {
	$tmp=substr($_REQUEST['extra'], 1, strlen($_REQUEST['extra'])-1);
	unset($_REQUEST['extra']);
	foreach (explode('/', $tmp) as $var) {
		$parts=explode('=', $var);
		if (count($parts)==1) {
			$remainder.='/'.$parts[0];
		}
		else {
			$_REQUEST[$parts[0]]=$parts[1];
		}
	}
}
else {
	unset($_REQUEST['extra']);
}
if (!isset($_REQUEST['f'])) {
	header('Content-type: application/json; charset=utf-8');
	die('{"error":"'.addslashes(__('no function name supplied')).'"}');
}
$_REQUEST['_remainder']=$remainder;
// }
// { check plugin to use
if (isset($_REQUEST['p'])) {
	if (!isset($PLUGINS[$_REQUEST['p']])) {
		header('Content-type: application/json; charset=utf-8');
		die('{"error":"'.addslashes(__('plugin not installed')).'"}');
	}
	require_once SCRIPTBASE.'ww.plugins/'.$_REQUEST['p'].'/api.php';
	if (strpos($_REQUEST['f'], 'admin')===0) {
		if (!Core_isAdmin()) {
			header('Content-type: application/json; charset=utf-8');
			die(
				'{"error":"'.addslashes(__('you are not logged in as an admin')).'"}'
			);
		}
		require_once SCRIPTBASE.'ww.admin/admin_libs.php';
		require_once SCRIPTBASE.'ww.plugins/'.$_REQUEST['p'].'/api-admin.php';
	}
	$plugin=preg_replace(
		'/[^a-zA-Z]/',
		'',
		ucwords(str_replace('-', ' ', $_REQUEST['p']))
	);
}
else {
	$plugin='Core';
	require_once 'api-funcs.php';
	if (strpos($_REQUEST['f'], 'admin')===0) {
		if (!Core_isAdmin()) {
			header('Content-type: application/json; charset=utf-8');
			die('{"error":"you are not logged in as an admin"}');
		}
		require_once SCRIPTBASE.'ww.admin/admin_libs.php';
		require_once 'api-admin.php';
	}
}
// }

$func=ucfirst($plugin).'_'.$_REQUEST['f'];
if (!function_exists($func)) {
	header('Content-type: application/json; charset=utf-8');
	die(
		'{"error":"'
		.addslashes(__('function %1 does not exist', array($func), 'core'))
		.'"}'
	);
}

$res=$func($_REQUEST);
header('Content-type: application/json; charset=utf-8');
if (@$_REQUEST['callback']) {
	echo $_REQUEST['callback'].'('.json_encode($res).')';
}
else {
	echo json_encode($res);
}
