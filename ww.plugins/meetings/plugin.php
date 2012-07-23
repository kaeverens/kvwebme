<?php

// { plugin config

$plugin=array(
	'admin' => array( // {
		'menu' => array(
			'Meetings'=>
				'/ww.admin/plugin.php?_plugin=meetings&amp;_page=meetings'
		)
	), // }
	'dependencies'=>'forms',
	'description'=>function() {
		return __('Create meetings for your employees and customers.');
	},
	'name' => function() {
		return __('Meetings');
	},
	'version'=>3
);

// }
