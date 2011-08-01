<?php

/**
 * plugin.php, KV-Webme Ratings Plugin
 *
 * plugin file for the ratings plugin
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    2.0
 */

// { plugin array
$plugin = array(
	'name'		=>	'Ratings',
	'version'	=>	2,
	'description'	=>	'Rate anything',
  'frontend'=>array(
		'template_functions'=>array(
			'RATINGS'=>array(
				'function' => 'ratings_template_function'
			)   
		)   
	)
);
// }

/**
 * ratings_template_function
 *
 * replaces {{RATINGS}} with a rating interface
 */
function ratings_template_function($vars) {
	$name = @$vars[ 'name' ];
	$type = @$vars[ 'type' ];

	if ($name == '') {
		return '';
	}

	$script = '$(function(){$(".ratings").ratings();});';

	WW_addScript('/ww.plugins/ratings/ratings.js');
	WW_addInlineScript($script);

	return '<div class="ratings" id="' . $name . '" type="' . $type . '">'
		. 'ratings</div>';
}
