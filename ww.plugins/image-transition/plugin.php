<?php
$plugin=array(
	'name' => 'Image Transitions',
	'admin' => array(
		'widget' => array(
			'form_url'   => '/ww.plugins/image-transition/admin/widget-form.php',
			'js_include' => '/ww.plugins/image-transition/admin/widget.js'
		)
	),
	'description' => 'Show all images in a directory, transitioning between them',
	'frontend' => array(
		'widget' => 'showImageTransition'
	),
	'version' => '2'
);
function showImageTransition($vars=null) {
	require_once SCRIPTBASE.'ww.plugins/image-transition/frontend/index.php';
	return ImageTransition_show($vars);
}
