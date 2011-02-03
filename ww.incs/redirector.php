<?php
/**
	* redirect the browser to an appropriate page (for logins, shops, etc)
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     http://webworks.ie/
	*/

require_once 'common.php';

$id=isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;
$type=$_REQUEST['type'];
$url='/';
switch($type){
	case 'loginpage': // {
		$p=Page::getInstanceByType('privacy');
		if (!$p) {
			$url='/';
		}
		else {
			$url=$p->getRelativeUrl();
		}
		if (isset($_REQUEST['login_referer'])) {
			$url.='?login_referer='.urlencode($_REQUEST['login_referer']);
		}
		$url.='#Login';
	break; // }
	default: // {
		$get=array();
		foreach ($_GET as $k=>$v) {
			if ($k!='type') {
				$get[]=urlencode($k).'='.urlencode($v);
			}
		}
		$p=Pages::getInstancesByType($type);
		if (!count($p)) {
			$url='/';
		}
		else {
			$url=$p->pages[0]->getRelativeUrl();
		}
		if (count($get)) {
			$url.='?'.join('&', $get);
		}
		// }
}
redirect($url);
