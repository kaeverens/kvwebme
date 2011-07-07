<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function image_gallery_template_images($params,&$smarty){
	$pagedata=$smarty->_tpl_vars['pagedata'];
	$args=array(
		'image_gallery_x'=>'cols',
		'image_gallery_y'=>'rows',
		'image_gallery_hover'=>'hover',
		'image_gallery_autostart'=>'slideshow',
		'image_gallery_slidedelay'=>'slideshowTime',
		'image_gallery_ratio'=>'ratio',
		'image_gallery_links'=>'links',
		'image_gallery_ratio'=>'ratio',
		'image_gallery_width'=>'galleryWidth',
		'image_gallery_thumbsize'=>'thumbsize'
	);
	$new_args=array();
	foreach($args as $name=>$value){
		if(isset($pagedata->vars[$name]))
			$new_args[$value]=$pagedata->vars[$name];
	}
	if(!empty($params['hover']))
		$new_args['hover']=$params['hover'];
	if(!empty($params['display']))
		$new_args['display']=$params['display'];
	$html='<div class="ad-gallery"';
	foreach($new_args as $arg=>$value){
		$html.=' '.$arg.'="'.$value.'"';
	}
	$html.='></div>';
	return $html;
}
function image_gallery_template_image($params,&$smarty){
	$pagedata=$smarty->_tpl_vars['pagedata'];
	$width=(empty($pagedata->vars['image_gallery_image_x']))?
		'':
		' imageWidth="'.$pagedata->vars['image_gallery_image_x'].'"';
	$height=(empty($pagedata->vars['image_gallery_image_y']))?
		'':
		' imageHeight="'.$pagedata->vars['image_gallery_image_y'].'"';
	$effect=(empty($pagedata->vars['image_gallery_effect']))?
		'':
		' effect="'.$pagedata->vars['image_gallery_effect'].'"';	
	$html='<div id="gallery-image"'.$width.$height.$effect.'>'
		. '<div class="ad-image">'
			. '<span class="dark-background ad-image-description" style="display:none"></span>'
			. '<img src="">'
		. '</div>'
		. '</div>';
	return $html;
}
?>
