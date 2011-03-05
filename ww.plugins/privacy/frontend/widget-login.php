<?php
echo '<div id="userauthentication-widget" widget-id="'
	.$widget_id.'-'.$vars->id.'">'
	.'log in with <button>email and password</button>';
if (isset($vars->external_login)
	&& $vars->external_login=='1'
) {
	echo ' or '
		.'<button appid="'.$vars->fbappid.'">Facebook</button>';
}
echo '</div>';
