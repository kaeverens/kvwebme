<?php
$c='<div id="userauthentication-widget" widget-id="'
	.$widget_id.'-'.$vars->id.'"><ul>'
	.'<li>Hi, Guest</li>'
	.'<li class="userauthentication-login"><button>Login</button></li>'
	.'<li class="userauthentication-register"><button href="'.Page::getinstance($vars->id)->getRelativeUrl().'">Register</button></li>';
if (isset($vars->external_login)
	&& $vars->external_login=='1'
) {
	$c.='<li class="userauthentication-facebook"><img src="/ww.plugins/privacy/i/facebook.png" appid="'.$vars->fbappid
		.'" class="facebook" alt="Facebook"/></li>';
}
$c.='</ul></div>';
