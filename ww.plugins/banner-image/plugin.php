<?php
/**
	* definition file for Banners plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => function() {
		return __('Banners');
	},
	'admin' => array(
		'menu' => array(
			'Misc>Banners'  => 'plugin.php?_plugin=banner-image&amp;_page=index'
		),
	),
	'description' => function() {
		return __('HTML snippet or image.');
	},
	'frontend' => array(
		'template_functions' => array(
			'BANNER' => array(
				'function' => 'BannerImage_showBanner'
			)
		),
		'widget' => 'BannerImage_showBanner'
	),
	'version' => '3'
);
/**
	* __('Misc')
	* __('Banners')
	*/
// }
$banner_image_types=array('jpg','gif','png');

function BannerImage_getImgHtml($id, $hide_message=false) {
	global $banner_image_types;
	$type='';
	foreach ($banner_image_types as $t) {
		if (file_exists(USERBASE.'/f/skin_files/banner-image/'.$id.'.'.$t)) {
			$type=$t;
		}
	}
	if (!$type) {
		return $hide_message?'':__('no image uploaded');
	}
	return '<img src="/f/skin_files/banner-image/'.$id.'.'.$type.'" />';
}
function BannerImage_showBanner($vars=null) {
	require_once SCRIPTBASE.'ww.plugins/banner-image/frontend/banner-image.php';
	return show_banner($vars);
}
