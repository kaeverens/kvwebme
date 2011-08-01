<?php

require_once 'basics.php';

header('Content-type: text/json');
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
	$plugin=preg_replace('/[^a-zA-Z]/',
		'',
		ucwords(str_replace('-', ' ', $_REQUEST['p']))
	);
}
else {
	$plugin='Core';
	require_once 'api-funcs.php';
}
// }

$func=$plugin.'_'.$_REQUEST['f'];
if (!function_exists($func)) {
echo $func;
	die('{"error":"function does not exist"}');
}

echo json_encode($func($_REQUEST));
