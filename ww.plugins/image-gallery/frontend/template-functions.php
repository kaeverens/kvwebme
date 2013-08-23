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

// { ImageGallery_nav

/**
	* function for showing a list of gallery pages, as well as left/right arrows
	*
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the nav menu
  */
function ImageGallery_nav($params, $smarty) {
	return '<div id="image-gallery-nav"><table>'
		.'<tr><td style="text-align:left;"><div id="prev-link"/></td>'
		.'<td class="pagelinks"></td>'
		.'<td style="text-align:right"><div id="next-link"/></td></tr>'
		.'</table></div>';
}

// }
// { ImageGallery_templateImages

/**
  * function for returning a gallery wrapper with embedded HTML parameters
  *
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the gallery wrapper
  */
function ImageGallery_templateImages($params, $smarty) {
	$pagedata=$smarty->smarty->tpl_vars['pagedata']->value;
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
		'image_gallery_thumbsize'=>'thumbsize',
		'image_gallery-hide-sidebar'=>'hidesidebar'
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

// }
// { ImageGallery_templateImage

/**
  * function for returning a single image to the image gallery
  *
  * @param array $params  any parameters passed through the function
	* @param array &$smarty the active Smarty instance
  *
  * @return string HTML of the image and its wrapper
  */
function ImageGallery_templateImage($params, $smarty) {
	$pagedata=$smarty->smarty->tpl_vars['pagedata']->value;
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
		.'<span class="image"><img src=""></span>'
		.'</div>'
		.'</div>';
	return $html;
}

// }
