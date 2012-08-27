<?php
/**
	* plugin file for onetouchcontact
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
	'name' => 'OneTouchContact',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/onetouchcontact/admin/widget-form.php'
		)
	),
	'description' => 'DEPRECATED. use the "Mailing Lists" plugin instead.',
	'frontend' => array(
		'widget' => 'Onetouchcontact_widget',
	)
);

/**
	* Onetouchcontact_widget
	*
	* @param array $vars variables
	*
	* @return html
	*/
function Onetouchcontact_widget($vars) {
	require_once SCRIPTBASE.'ww.plugins/onetouchcontact/frontend/widget.php';
	return Onetouchcontact_widgetShow($vars);
}
