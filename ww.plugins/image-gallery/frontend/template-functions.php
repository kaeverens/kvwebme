<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function image_gallery_template_images($params,&$smarty){
	$pagedata=$smarty->_tpl_vars['pagedata'];
	$rows=(empty($pagedata->vars['image_gallery_y']))?
		'':
		' rows="'.$pagedata->vars['image_gallery_y'].'"';
	$columns=(empty($pagedata->vars['image_gallery_x']))?
		'':
		' cols="'.$pagedata->vars['image_gallery_x'].'"';
	$hover=(empty($params['hover']))?
		'':
		' hover="'.$params['hover'].'"';
	if($hover==''&&!empty($pagedata->vars['image_gallery_hover'])){
		$hover=' hover="'.$pagedata->vars['image_gallery_hover'].'"';
	}
	$slideshow=(empty($pagedata->vars['image_gallery_autostart']))?
		'':
		' slideshow="'.$pagedata->vars['image_gallery_autostart'].'"';
	$slideshowTime=(empty($pagedata->vars['image_gallery_slidedelay']))?
		'':
		' slideshowtime="'.$pagedata->vars['image_gallery_slidedelay'].'"';
	$display=(empty($params['display']))?'':' display="'.$params['display'].'"';
	$html='<div class="ad-gallery"'.$display.$hover.$columns.$rows.$slideshow.$slideshowTime.' thumbsize="'
		.$pagedata->vars['image_gallery_thumbsize'].'">';
	$html.='</div>';
	return $html;
}
function image_gallery_template_image($params,&$smarty){
	$pagedata=$smarty->_tpl_vars['pagedata'];
	$width=(empty($pagedata->vars['image_gallery_image_x']))?
		'':
		' width="'.$pagedata->vars['image_gallery_image_x'].'"';
	$height=(empty($pagedata->vars['image_gallery_image_y']))?
		'':
		' height="'.$pagedata->vars['image_gallery_image_y'].'"';
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
