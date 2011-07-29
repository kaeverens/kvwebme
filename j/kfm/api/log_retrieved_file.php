<?php
require_once 'Log.php';
if(!function_exists('Core_flushBuffer')){
function Core_flushBuffer($type){
	$log = &Log::singleton('file',USERBASE.'log.txt',$type,array('locking'=>true,'timeFormat'=>'%Y-%m-%d %H:%M:%S'));
	$length=$GLOBALS['filesize'];
	$num_queries=isset($GLOBALS['db'])?$GLOBALS['db']->num_queries:0;
	switch($type){
		case 'file': // {
			$location=$_SERVER['REQUEST_URI'];
			break;
		// }
		case 'menu': // {
			$location='menu';
			break;
		// }
		case 'page': // {
			$location=$GLOBALS['PAGEDATA']->id.'|'.$GLOBALS['PAGEDATA']->getRelativeUrl();
			break;
		// }
		default: // {
			$location='unknown_type_'.$type;
		//}
	}
	$log->log(
		$_SERVER['REMOTE_ADDR']
		.'	'.$location
		.'	'.$_SERVER['HTTP_USER_AGENT']
		.'	'.$_SERVER['HTTP_REFERER']
		.'	'.memory_get_peak_usage()
		.'	'.$length
		.'	'.(microtime(true)-START_TIME)
		.'	'.$num_queries
	);
	ob_flush();
}
}
Core_flushBuffer('file');
