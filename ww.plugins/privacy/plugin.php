<?php
$plugin=array(
	'name' => 'User Authentication',
	'admin' => array(
		'page_type' => 'privacy_admin',
		'page_panel' => array(
			'name' => 'Privacy',
			'function' => 'privacy_show_page_panel'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/privacy/admin/widget.php'
		)
	),
	'description' => 'User authentication, page protection.',
	'frontend' => array(
		'page_type' => 'privacy_front',
		'page_display_test' => 'privacy_page_test',
		'widget' => 'UserAuthentication_showWidget'
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
function UserAuthentication_showWidget($vars=null, $widget_id) {
	if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
		require_once SCRIPTBASE.'ww.plugins/privacy/frontend/widget-login.php';
		WW_addScript('/ww.plugins/privacy/frontend/widget-login.js');
		return $c;
	}
	return '<div id="userauthentication-widget">Logged in as <strong>'
		.$_SESSION['userdata']['name'].'</strong>'
		.' [<a href="/?logout">log out</a>]</div>';
}
