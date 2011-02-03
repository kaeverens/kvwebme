<?php
$plugin=array(
	'name' => 'Content Snippets',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/content-snippet/admin/widget-form.php',
			'js_include' => '/ww.plugins/content-snippet/admin/widget.js'
		)
	),
	'description' => 'Add small static HTML snippets to any panel - address, slogan, footer, image, etc.',
	'frontend' => array(
		'widget' => 'showContentSnippet'
	),
	'version' => '3'
);
function showContentSnippet($vars=null){
	include_once SCRIPTBASE.'ww.plugins/content-snippet/frontend/index.php';
	return show_content_snippet($vars);
}
