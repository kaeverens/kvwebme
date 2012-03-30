<?php
$plugin=array(
	'name' => 'Search',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/search/admin/widget-form.php'
		)
	),
	'description' => 'Add a search box to a panel.',
	'frontend' => array(
		'widget' => 'search_displayInputBox'
	),
	'version'=>0
);

function search_displayInputBox($vars) {
	require SCRIPTBASE.'ww.plugins/search/frontend/widget.php';
	return $html;
}
