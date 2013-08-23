<?php
/**
	* front controller for WebME files
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
	'name' => 'Feed Reader',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/messaging-notifier/admin/widget-form.php',
			'js_include' => '/ww.plugins/messaging-notifier/admin/widget.js'
		)
	),
	'description' => 'Show messages from feeds such as twitter, rss, phpbb3',
	'frontend' => array(
		'widget' => 'MessagingNotifier_showWidget'
	),
	'version' => '3'
);
// }

// { MessagingNotifier_showWidget

/**
	* MessagingNotifier_showWidget
	*
	* @param array $vars vars
	* @param int   $pid  ID
	*
	* @return html
	*/
function MessagingNotifier_showWidget($vars, $pid) {
	require_once SCRIPTBASE.'ww.plugins/messaging-notifier/frontend/index.php';
	return Aggregator_show($vars);
}

// }
