<?php
/**
  * ImageGallery template functions
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';

/**
	* function for showing a list of gallery pages, as well as left/right arrows
	*
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the nav menu
  */
function ImageGallery_nav($params, $smarty) {
	return '<div id="image-gallery-nav"><table style="width:100%">'
		.'<tr><td style="text-align:left;"><div id="prev-link"/></td>'
		.'<td class="pagelinks" style="width:90%;text-align:center"></td>'
		.'<td style="text-align:right"><div id="next-link"/></td></tr>'
		.'</table></div>';
}

/**
  * function for returning a gallery wrapper with embedded HTML parameters
  *
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the gallery wrapper
  */
function ImageGallery_templateImages($params, $smarty) {
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
	foreach ($args as $name=>$value) {
		if (isset($pagedata->vars[$name])) {
			$new_args[$value]=$pagedata->vars[$name];
		}
	}
	foreach ($params as $k=>$v) {
		$new_args[$k]=$v;
	}
	$html='<div class="ad-gallery"';
	foreach ($new_args as $arg=>$value) {
		$html.=' '.$arg.'="'.$value.'"';
	}
	$html.='></div>';
	return $html;
}

/**
  * function for returning a single image to the image gallery
  *
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the image and its wrapper
  */
function ImageGallery_templateImage($params, $smarty) {
	$pagedata=$smarty->_tpl_vars['pagedata'];
	$width=(empty($pagedata->vars['image_gallery_image_x']))
		?''
		:' imageWidth="'.$pagedata->vars['image_gallery_image_x'].'"';
	$height=(empty($pagedata->vars['image_gallery_image_y']))
		?''
		:' imageHeight="'.$pagedata->vars['image_gallery_image_y'].'"';
	$effect=(empty($pagedata->vars['image_gallery_effect']))
		?''
		:' effect="'.$pagedata->vars['image_gallery_effect'].'"';	
	$html='<div id="gallery-image"'.$width.$height.$effect.'>'
		.'<div class="ad-image">'
		.'<span class="dark-background ad-image-description caption" style="display:none">'
		.'</span>'
		.'<img src="">'
		.'</div>'
		.'</div>';
	return $html;
}
