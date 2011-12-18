<?php
/**
	* plugin file for the mp3 plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { plugin config
$plugin = array( 
	'name'	=>	'Mp3 Plugin',
	'version'	=>	1,
	'description'	=>	'Allows mp3 files to be stored and played.',
	'admin'	=>	array(
		'widget' => array(
			'form_url' => '/ww.plugins/mp3/admin/widget-form.php',
			'js_include' => '/ww.plugins/mp3/admin/widget.js'
		)
	),
	'frontend'	=>	array(
		'widget'=>'MP3_frontendWidget',
	)
);
// }

require SCRIPTBASE.'ww.plugins/mp3/frontend/show.php';
