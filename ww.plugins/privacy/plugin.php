<?php
$plugin=array(
	'name' => 'Privacy',
	'admin' => array(
		'page_type' => 'privacy_admin',
		'page_panel' => array(
			'name' => 'Privacy',
			'function' => 'privacy_show_page_panel'
		)
	),
	'description' => 'User authentication, page protection.',
	'frontend' => array(
		'page_type' => 'privacy_front',
		'page_display_test' => 'privacy_page_test'
	),
	'version'=>0
);

function privacy_front($PAGEDATA){
	require SCRIPTBASE.'ww.plugins/privacy/frontend/page_type.php';
	return $html;
}
function privacy_admin($page,$page_vars){
	require SCRIPTBASE.'ww.plugins/privacy/admin/page_type.php';
	return $html;
}
function privacy_show_page_panel($page,$page_vars){
	require SCRIPTBASE.'ww.plugins/privacy/admin/privacy_show_page_panel.php';
}
function privacy_page_test($pagedata){
	require SCRIPTBASE.'ww.plugins/privacy/frontend/privacy_page_test.php';
	return $allowed;
}
