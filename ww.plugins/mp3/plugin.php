<?php

/**
 * plugin.php, KV-Webme MP3 Plugin
 *
 * plugin file for the mp3 plugin
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * plugin array, which holds the configuartion
 * options for the plugin
 */
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
		'widget'=>'mp3_frontend_widget',
	)
);
require SCRIPTBASE.'ww.plugins/mp3/frontend/show.php';
