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
if ($_REQUEST['extra']!='') {
	$tmp=substr($_REQUEST['extra'], 1, strlen($_REQUEST['extra'])-1);
	unset($_REQUEST['extra']);
	foreach (explode('/', $tmp) as $var) {
		list($k, $v)=explode('=', $var);
		$_REQUEST[$k]=$v;
	}
}
else {
	unset($_REQUEST['extra']);
}
// }
// { check plugin to use
if (isset($_REQUEST['p'])) {
	if (!isset($PLUGINS[$_REQUEST['p']])) {
		die('{"error":"plugin not installed"}');
	}
	require_once SCRIPTBASE.'ww.plugins/'.$_REQUEST['p'].'/api.php';
	if (strpos($_REQUEST['f'], 'admin')===0) {
		if (!Core_isAdmin()) {
			die('{"error":"you are not logged in as an admin"}');
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
			die('{"error":"you are not logged in as an admin"}');
		}
		require_once SCRIPTBASE.'ww.admin/admin_libs.php';
		require_once 'api-admin.php';
	}
}
// }

$func=ucfirst($plugin).'_'.$_REQUEST['f'];
if (!function_exists($func)) {
	die('{"error":"function '.$func.' does not exist"}');
}

$res=$func($_REQUEST);
header('Content-type: text/json');
echo json_encode($res);
