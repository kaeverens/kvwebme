<?php
$c='<div id="userauthentication-widget" widget-id="'
	.$widget_id.'-'.$vars->id.'">'
	.'log in with <button>email/password</button>';
if (isset($vars->external_login)
	&& $vars->external_login=='1'
) {
	$c.=' or '
		.'<img src="/ww.plugins/privacy/i/facebook.png" appid="'.$vars->fbappid
		.'" class="facebook" alt="Facebook"/>';
}
$c.='</div>';
