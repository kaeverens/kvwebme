<?php
/**
  * ImageGallery gallery generation script
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
  * function for generating and returning a gallery's HTML
  *
  * @param array $PAGEDATA Page object
  *
  * @return string HTML of the gallery
  */
function ImageGallery_show($PAGEDATA) {
	$vars=$PAGEDATA->vars;
	$c=$PAGEDATA->render();
	$dir_id=kfm_api_getDirectoryId(
		preg_replace('/^\//', '', $vars['image_gallery_directory'])
	);
	$images=kfm_loadFiles($dir_id);
	$images=$images['files'];
	$n=count($images);
	if (!isset($vars['footer'])) {
		$vars['footer']='';
	}
	if ($n) {
		// { if template doesn't exist, create it
		$template=USERBASE.'ww.cache/image-gallery/';
		@mkdir($template);
		$template.=$PAGEDATA->id;
		if (!file_exists($template) || !filesize($template)) {
			$thtml=@$PAGEDATA->vars['gallery-template'];
			if (!$thtml) {
				$thtml=file_get_contents(dirname(__FILE__).'/../admin/types/list.tpl');
			}
			file_put_contents(
				$template,
				$thtml
			);
		}
		// }
		// { display the template
		require_once SCRIPTBASE.'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
		require SCRIPTBASE.'ww.plugins/image-gallery/frontend/template-functions.php';
		$smarty=new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		@mkdir(USERBASE.'/ww.cache/templates_c'); 
		@mkdir(USERBASE.'/ww.cache/templates_c/image-gallery');
		$smarty->assign('pagedata', $PAGEDATA);
		$smarty->register_function('GALLERY_IMAGE',  'ImageGallery_templateImage');
		$smarty->register_function('GALLERY_IMAGES', 'ImageGallery_templateImages');
		$smarty->register_function('GALLERY_NAV',    'ImageGallery_nav');
		$smarty->left_delimiter='{{';
		$smarty->right_delimiter='}}';
		$c.=$smarty->fetch(
			USERBASE.'ww.cache/image-gallery/'.$PAGEDATA->id
		);
		WW_addScript('/ww.plugins/image-gallery/frontend/gallery4.js');
		WW_addCSS('/ww.plugins/image-gallery/frontend/gallery.css');
		// }
		return $c.$vars['footer'];
	}
	else {
		return $c.'<em>gallery "'.$vars['image_gallery_directory']
			.'" not found.</em>'.$vars['footer'];
	}
}

function GalleryWidget_show($vars){
	$id=$vars->id;
	// { get data from widget db
	$vars=dbRow('select * from image_gallery_widget where id="'.$id.'"');
	// }
	$dir_id=kfm_api_getDirectoryId(
		preg_replace('/^\//', '', $vars['directory'])
	);
	$images=kfm_loadFiles($dir_id);
	$images=$images['files'];
	$n=count($images);
	if ($n) {
		// { if template doesn't exist, create it
		$template=USERBASE.'ww.cache/image-gallery-widget/';
		@mkdir($template, 0777, true);
		$template.=$id;
		$thtml=file_get_contents(
			SCRIPTBASE.'ww.plugins/image-gallery/admin/types/'.
				strtolower($vars['gallery_type'])
			.'.tpl'
		);
		if (!$thtml) {
			$thtml=file_get_contents(dirname(__FILE__).'/../admin/types/list.tpl');
		}
		file_put_contents(
			$template,
			$thtml
		);
		// }
		// { display the template
		require_once SCRIPTBASE.'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
		require SCRIPTBASE.'ww.plugins/image-gallery/frontend/template-functions.php';
		$smarty=new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		@mkdir(USERBASE.'/ww.cache/templates_c'); 
		@mkdir(USERBASE.'/ww.cache/templates_c/image-gallery-widget');
		$smarty->register_function('GALLERY_IMAGE', 'ImageGallery_templateImage');
		$smarty->register_function('GALLERY_IMAGES', 'ImageGallery_templateImages');
		$smarty->left_delimiter='{{';
		$smarty->right_delimiter='}}';
		$c.=$smarty->fetch(
			USERBASE.'ww.cache/image-gallery-widget/'.$id
		);
		// { quick hack to add the options rather than
		// writing a whole new function in php
		$script='
		Gallery.options.directory="'.(int)$vars['directory'].'";
		Gallery.options.thumbsize='.(int)$vars['thumbsize'].';
		Gallery.options.imageWidth='.(int)$vars['image_size'].';
		Gallery.options.imageHeight='.(int)$vars['image_size'].';
		Gallery.gallery()
			.attr("cols","'.$vars['columns'].'")
			.attr("rows","'.$vars['rows'].'");
		';
		// }
		WW_addScript('/ww.plugins/image-gallery/frontend/gallery.js');
		WW_addInlineScript($script);
		WW_addCSS('/ww.plugins/image-gallery/frontend/gallery.css');
		// }
		return $c;
	}
	else {
		return '<em>gallery "'.$vars['directory']
			.'" not found or empty.</em>';
	}
}
