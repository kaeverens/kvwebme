<?php
/**
	* widget for logins
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!@$vars->id) {
	$c.='<em>'
		.__('error: no user authentication page chosen for this widget')
		.'</em>';
	return;
}
$c='<div id="userauthentication-widget" widget-id="'
	.$widget_id.'-'.$vars->id.'"><ul>'
	.'<li>'.__('Hi, Guest').'</li>'
	.'<li class="userauthentication-login"><button>'.__('Login').'</button></li>'
	.'<li class="userauthentication-register"><button href="'
	.Page::getInstance($vars->id)->getRelativeUrl().'">'.__('Register')
	.'</button></li>';
if (isset($vars->external_login)
	&& $vars->external_login=='1'
) {
	$c.='<li class="userauthentication-facebook">'
		.'<img src="/ww.plugins/privacy/i/facebook.png" appid="'.$vars->fbappid
		.'" class="facebook" alt="Facebook"/></li>';
}
$c.='</ul><span class="clear"></span></div>';
