<?php
/**
	* plugin file for the ratings plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { plugin array
$plugin = array(
	'name' => function() {
		return __('Ratings');
	},
	'version'	=>	2,
	'description'	=>	function() {
		return __('Rate anything');
	},
  'frontend'=>array(
		'template_functions'=>array(
			'RATINGS'=>array(
				'function' => 'Ratings_templateFunction'
			)   
		)   
	)
);
// }

/**
 * Ratings_templateFunction
 *
 * replaces {{RATINGS}} with a rating interface
 *
 * @param array $vars settings
 *
 * @return string html
 */
function Ratings_templateFunction($vars) {
	$name = @$vars[ 'name' ];
	$type = @$vars[ 'type' ];

	if ($name == '') {
		return '';
	}

	$script = '$(function(){$(".ratings").ratings();});';

	WW_addScript('/j/jquery.tooltip.min.js');
	WW_addScript('ratings/ratings.js');
	WW_addInlineScript($script);

	return '<div class="ratings" id="' . $name . '" type="' . $type . '">'
		.__('ratings', 'core').'</div>';
}
