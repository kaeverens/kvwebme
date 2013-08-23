<?php
/**
	* plugin config for content snippets
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { plugin config
$plugin=array(
	'name' => function() {
		return __('Content Snippets');
	},
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/content-snippet/admin/widget-form.php',
			'js_include' => '/ww.plugins/content-snippet/admin/widget.js'
		)
	),
	'description' => function()	{
		return __(
			'Add small static HTML snippets to any panel - address, slogan,'
			.' footer, image, etc.'
		);
	},
	'frontend' => array(
		'widget' => 'ContentSnippet_show'
	),
	'version' => '3'
);
// }

/**
	* show a content snippet
	*
	* @param array $vars array of parameters
	*
	* @return string contentsnippet
	*/
function ContentSnippet_show($vars=null) {
	require_once SCRIPTBASE.'ww.plugins/content-snippet/frontend/index.php';
	return ContentSnippet_show2($vars);
}
