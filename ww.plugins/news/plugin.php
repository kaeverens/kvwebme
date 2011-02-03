<?php
/*
        Webme News Plugin v0.1
        File: plugin.php
        Developer: Conor Mac Aoidh <http://macaoidh.name>
        Report Bugs: <conor@macaoidh.name>
*/

$plugin=array(
	'name' => 'News',
	'admin' => array(
		'page_type' => 'news_admin',
		'widget' => array(
			'form_url' => '/ww.plugins/news/admin/widget-form.php'
		)
	),
	'description' => 'Create news items from sub-pages.',
	'frontend' => array(
		'page_type' => 'news_front',
		'widget' => 'news_showWidget'
	),
	'version'=>1
);

function news_admin($page, $vars) {
	require SCRIPTBASE.'ww.plugins/news/admin/display.php';
	return $html;
}

function news_front($PAGEDATA) {
	require SCRIPTBASE.'ww.plugins/news/frontend/display.php';
	return $html;
}

function news_showWidget($vars) {
	require SCRIPTBASE.'ww.plugins/news/frontend/widget.php';
	return '<div class="news-wrapper">'.$html.'</div>';
}
