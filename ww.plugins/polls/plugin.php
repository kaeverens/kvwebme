<?php
/**
	* definition file for Polls plugin
	*
	* PHP version 5.4.6
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

$plugin=array(
	'name' => 'Polls',
	'admin' => array(
		'menu' => array(
			'Communication>Polls'=>'plugin.php?_plugin=polls&amp;_page=index'
		)
	),
	'description' => 'Create your own polls with this plugin.',
	'frontend' => array(
		'template_functions' => array(
			'POLL' => array(
				'function' => 'Polls_pollDisplay'
			)
		),
		'widget' => 'Polls_pollDisplay'
	)
);

// { Polls_pollDisplay

/**
	* display a poll
	*
	* @return null
	*/
function Polls_pollDisplay() {
	require_once SCRIPTBASE . 'ww.plugins/polls/frontend/polls.php';
	return poll_display();
}

// }
