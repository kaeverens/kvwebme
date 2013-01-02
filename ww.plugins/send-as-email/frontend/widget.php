<?php
/**
	* SendAsEmail_showWidget
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { SendAsEmail_showWidget

/**
	* SendAsEmail_showWidget
	*
	* @param array $vars variables
	*
	* @return null
	*/
function SendAsEmail_showWidget($vars) {
	$template=$vars->template;
	$url=$_SERVER['REQUEST_URI'];
	if (strpos($url, '&__t=') !==false || strpos($url, '?__t=') !==false) {
		$url=preg_replace('/[\?\&]__t=[^\&]*/', '', $url);
	}
	$url.='&__t='.$template;
	if (strpos($url, '&') !== false && strpos($url, '?') === false) {
		$url=preg_replace('/&/', '?', $url, 1);
	}
	echo '<div class="sendasemail-print"><a href="'.$url.'">Print Version</a>'
		.'</div>'
		.'<div class="sendasemail-sendasemail">'
		.'<a href="javascript:;" onclick="sendasemail_send(\''.$template.'\')">'
		.'Send as Email</a></div>';
	WW_addScript('send-as-email/frontend/widget.js');
}

// }
