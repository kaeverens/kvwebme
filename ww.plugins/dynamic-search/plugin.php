<?php
/*
	Webme Dynamic Search Plugin v0.2
	File: plugin.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

$plugin=array(
	'name' => 'Dynamic Search',
	'description' => 'Allows you to search certain sections of the website dynamically.',
        'admin' => array(
                'page_type' => 'dynamic_search_admin'
        ),
	'frontend' => array(
		'page_type' => 'dynamic_search_front'
	),
	'version' => '0.3'
);

function dynamic_search_front(){
	require SCRIPTBASE.'ww.plugins/dynamic-search/frontend/switch.php';
	return $html;
}

function dynamic_search_admin(){
	require SCRIPTBASE.'ww.plugins/dynamic-search/admin/page.php';
	return $html;
}
