<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function image_gallery_show($PAGEDATA){
	$vars=$PAGEDATA->vars;
	$c=$PAGEDATA->render();
	$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//','',$vars['image_gallery_directory']));
	$images=kfm_loadFiles($dir_id);
	$images=$images['files'];
	$n=count($images);
	if (!isset($vars['footer'])) {
		$vars['footer']='';
	}
	if($n){
		// { display the template
		require_once SCRIPTBASE.'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
		require SCRIPTBASE.'ww.plugins/image-gallery/frontend/template-functions.php';
		$smarty=new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		if(!file_exists(USERBASE.'/ww.cache/templates_c'))
			mkdir(USERBASE.'/ww.cache/templates_c'); 
		if(!file_exists(USERBASE.'/ww.cache/templates_c/image-gallery'))
			mkdir(USERBASE.'/ww.cache/templates_c/image-gallery');
		$smarty->assign('pagedata',$PAGEDATA);
		$smarty->register_function('GALLERY_IMAGE','image_gallery_template_image');
		$smarty->register_function('GALLERY_IMAGES','image_gallery_template_images');
		$smarty->left_delimiter='{{';
		$smarty->right_delimiter='}}';
		$c.=$smarty->fetch(
			USERBASE.'ww.cache/image-gallery/'.$PAGEDATA->id
		);
		WW_addScript('/ww.plugins/image-gallery/frontend/gallery.js');
		WW_addCSS('/ww.plugins/image-gallery/frontend/gallery.css');
		// }
		return $c.$vars['footer'];
	}
	else{
		return $c.'<em>gallery "'.$vars['image_gallery_directory'].'" not found.</em>'.$vars['footer'];
	}
}
