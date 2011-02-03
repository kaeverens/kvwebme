<?php
$plugin=array(
	'name' => 'Feed Reader',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/messaging-notifier/admin/widget-form.php',
			'js_include' => '/ww.plugins/messaging-notifier/admin/widget.js'
		)
	),
	'description' => 'Show messages from feeds such as twitter, rss, phpbb3',
	'frontend' => array(
		'widget' => 'messaging_notifier_show_widget'
	),
	'version' => '3'
);
function messaging_notifier_show_widget($vars){
	include_once SCRIPTBASE.'ww.plugins/messaging-notifier/frontend/index.php';
	return show_messaging_notifier($vars);
}
