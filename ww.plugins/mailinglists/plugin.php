<?php
/**
	* definition file for the WebME mailing lists plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$plugin=array(
	'name' => 'Mailing Lists',
	'admin' => array(
		'menu' => array(
			'Communication>Mailing Lists'=>'javascript:Core_screen(\'mailinglists\',\'Dashboard\')'
		)
	),
	'description' => 'Mailing lists',
	'frontend' => array(
		'widget' => 'MailingLists_widget'
	),
	'version' => '2'
);

function MailingLists_widget($vars) {
	$html='<div id="mailinglists-subscribe">'
		.'<table><tr><td class="__" lang-context="core">Email</td><td><input/></td>'
		.'<td class="__" lang-context="core">Choose your City/Country</td><td><select><option></option>';
	$lists=dbAll('select * from mailinglists_lists');
	foreach ($lists as $list) {
		$html.='<option value="'.$list['id'].'">'
			.htmlspecialchars($list['name']).'</option>';
	}
	$html.='</select></td>'
		.'<td><button class="__" lang-context="core">Subscribe</button></td></tr></table></div>';
	return $html;
}
