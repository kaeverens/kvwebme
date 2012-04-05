<?php
/**
	* frontend widget
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* show the onetouchcontact widget
	*
	* @param array $vars config
	*
	* @return string form
	*/
function Onetouchcontact_widgetShow($vars) {
	$form='<form class="onetouchcontact">Name<br/>'
		.'<input id="onetouchcontact-name"/><br/>Email<br/>'
		.'<input id="onetouchcontact-email"/><br/>';
	if ($vars->phone) {
		$form.='Phone<br/><input id="onetouchcontact-phone" /><br/>';
	}
	$form.='<input type="hidden" name="cid" value="'.$vars->cid.'"/>'
		.'<input type="hidden" name="mid" value="'.$vars->mid.'"/>'
		.'<div class="onetouchcontact-msg"></div>'
		.'<input class="submit" type="submit" value="subscribe"/></form>';
	WW_addScript('onetouchcontact/frontend/js.js');
	return $form;
}
