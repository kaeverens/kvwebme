<?php
/**
	* definition file for Banners plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'Banners',
	'admin' => array(
		'menu' => array(
			'Misc>Banners'  => 'index'
		),
	),
	'description' => 'HTML snippet or image.',
	'frontend' => array(
		'template_functions' => array(
			'BANNER' => array(
				'function' => 'showBanner'
			)
		),
		'widget' => 'showBanner'
	),
	'version' => '3'
);
// }
$banner_image_types=array('jpg','gif','png');

function banner_image_getImgHTML($id, $hide_message=false){
	global $banner_image_types;
	$type='';
	foreach ($banner_image_types as $t) {
		if (file_exists(USERBASE.'f/skin_files/banner-image/'.$id.'.'.$t)) {
			$type=$t;
		}
	}
	if (!$type) {
		return $hide_message?'':'no image uploaded';
	}
	return '<img src="/f/skin_files/banner-image/'.$id.'.'.$type.'" />';
}
function showBanner($vars=null) {
	require_once SCRIPTBASE.'ww.plugins/banner-image/frontend/banner-image.php';
	return show_banner($vars);
}
