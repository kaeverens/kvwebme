<?php
$plugin=array(
	'name' => 'OneTouchContact',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/onetouchcontact/admin/widget-form.php'
		)
	),
	'description' => 'DEPRECATED. use the "Mailing Lists" plugin instead.',
	'frontend' => array(
		'widget' => 'onetouchcontact_widget',
	)
);
function onetouchcontact_widget($vars){
	include_once SCRIPTBASE.'ww.plugins/onetouchcontact/frontend/widget.php';
	return onetouchcontact_widget_show($vars);
}
