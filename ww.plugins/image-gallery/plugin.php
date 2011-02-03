<?php
$plugin=array(
	'name' => 'Image Gallery',
	'admin' => array(
		'page_type' => 'image_gallery_admin_page_form'
	),
	'description' => 'Allows a directory of images to be shown as a gallery.',
	'frontend' => array(
		'page_type' => 'image_gallery_frontend'
	)
);
function image_gallery_admin_page_form($page,$vars){
	require_once dirname(__FILE__).'/admin/index.php';
	return $c;
}
function image_gallery_frontend($PAGEDATA){
	require_once dirname(__FILE__).'/frontend/show.php';
	return image_gallery_show($PAGEDATA);
}
