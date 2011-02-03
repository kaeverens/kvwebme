<?php
$plugin=array(
	'name' => 'Polls',
	'admin' => array(
		'menu' => array(
			'Communication>Polls'=>'index'
		)
	),
	'description' => 'Create your own polls with this plugin.',
	'frontend' => array(
		'template_functions' => array(
			'POLL' => array(
				'function' => 'pollDisplay'
			)
		),
		'widget' => 'pollDisplay'
	)
);
function pollDisplay(){
	include_once SCRIPTBASE . 'ww.plugins/polls/frontend/polls.php';
	return poll_display();
}
