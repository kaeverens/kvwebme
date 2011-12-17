<?php
/**
	* Webme Dynamic Search Plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor.macaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { plugin config
$plugin=array(
	'name' => 'Dynamic Search',
	'description' => 'Allows you to search certain sections of the website '
		.'dynamically.',
	 'admin' => array(
			'page_type' => 'DynamicSearch_admin'
		),
	'frontend' => array(
		'page_type' => 'DynamicSearch_front'
	),
	'version' => 3
);
// }

/**
	* dynamic search front
	*
	* @return string html
	*/
function DynamicSearch_front() {
	require SCRIPTBASE.'ww.plugins/dynamic-search/frontend/switch.php';
	return $html;
}

/**
	* dynamic search admin
	*
	* @return string html
	*/
function DynamicSearch_admin() {
	require SCRIPTBASE.'ww.plugins/dynamic-search/admin/page.php';
	return $html;
}
