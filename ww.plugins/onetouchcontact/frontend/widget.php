<?php
function onetouchcontact_widget_show($vars){
	$form='<form class="onetouchcontact">Name<br /><input id="onetouchcontact-name" /><br />Email<br /><input id="onetouchcontact-email" /><br />';
	if($vars->phone)$form.='Phone<br /><input id="onetouchcontact-phone" /><br />';
	$form.='<input type="hidden" name="cid" value="'.$vars->cid.'" /><input type="hidden" name="mid" value="'.$vars->mid.'" />';
	$form.='<div class="onetouchcontact-msg"></div><input class="submit" type="submit" value="subscribe" /></form>';
	WW_addScript('/ww.plugins/onetouchcontact/frontend/js.js');
	return $form;
}
