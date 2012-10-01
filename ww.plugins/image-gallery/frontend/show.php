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

// { ImageGallery_show

/**
  * function for generating and returning a gallery's HTML
  *
  * @param array $PAGEDATA Page object
  *
  * @return string HTML of the gallery
  */
function ImageGallery_show($PAGEDATA) {
	$vars=$PAGEDATA->vars;
	if (!isset($vars['image_gallery_directory'])) {
		return __('gallery directory has not yet been set');
	}
	$c=$PAGEDATA->render();
	// { check to see if there are files in the directory
	$hasImages=false;
	$dirname=USERBASE.'/f/'.$vars['image_gallery_directory'];
	if (file_exists($dirname)) {
		$dir=new DirectoryIterator($dirname);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$hasImages=true;
			break;
		}
	}
	// }
	if (!isset($vars['footer'])) {
		$vars['footer']='';
	}
	if ($hasImages) {
		// { if template doesn't exist, create it
		$template=USERBASE.'/ww.cache/image-gallery/';
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
		require_once SCRIPTBASE.'ww.incs/Smarty-3.1.12/libs/Smarty.class.php';
		require_once SCRIPTBASE
			.'ww.plugins/image-gallery/frontend/template-functions.php';
		$smarty=new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		@mkdir(USERBASE.'/ww.cache/templates_c'); 
		@mkdir(USERBASE.'/ww.cache/templates_c/image-gallery');
		$smarty->assign('pagedata', $PAGEDATA);
		$smarty->registerPlugin(
			'function', 'GALLERY_IMAGE', 'ImageGallery_templateImage'
		);
		$smarty->registerPlugin(
			'function', 'GALLERY_IMAGES', 'ImageGallery_templateImages'
		);
		$smarty->registerPlugin('function', 'GALLERY_NAV', 'ImageGallery_nav');
		$smarty->left_delimiter='{{';
		$smarty->right_delimiter='}}';
		$c.=$smarty->fetch(
			USERBASE.'/ww.cache/image-gallery/'.$PAGEDATA->id
		);
		WW_addScript('image-gallery/frontend/gallery4.js');
		WW_addCSS('/ww.plugins/image-gallery/frontend/gallery.css');
		// }
		return $c.$vars['footer'];
	}
	else {
		$dir=$vars['image_gallery_directory'];
		return $c.'<em>'
			.__('gallery "%1" not found.', array($dir), 'core')
			.$vars['footer'];
	}
}

// }
// { GalleryWidget_show

/**
	* show the ImageGallery widget
	*
	* @param array $vars parameters
	*
	* @return html
	*/
function GalleryWidget_show($vars) {
	if (!isset($vars->id) || !$vars->id) {
		return '';
	}
	$id=$vars->id;
	// { get data from widget db
	$vars=dbRow('select * from image_gallery_widget where id="'.$id.'"');
	// }
	// { check to see if there are files in the directory
	$hasImages=false;
	$dirname=USERBASE.'/f/'.$vars['directory'];
	if (file_exists($dirname)) {
		$dir=new DirectoryIterator($dirname);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$hasImages=true;
			break;
		}
	}
	// }
	if ($hasImages) {
		// { if template doesn't exist, create it
		$template=USERBASE.'/ww.cache/image-gallery-widget/';
		@mkdir($template, 0777, true);
		$template.=$id;
		if (!file_exists($template)) {
			if (!$vars['gallery_type']) {
				$vars['gallery_type']='grid';
			}
			$thtml=file_get_contents(
				SCRIPTBASE.'ww.plugins/image-gallery/admin/types/'
				.strtolower($vars['gallery_type']).'.tpl'
			);
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
		require_once SCRIPTBASE.'ww.incs/Smarty-3.1.12/libs/Smarty.class.php';
		require_once SCRIPTBASE
			.'ww.plugins/image-gallery/frontend/template-functions.php';
		$smarty=new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		@mkdir(USERBASE.'/ww.cache/templates_c'); 
		@mkdir(USERBASE.'/ww.cache/templates_c/image-gallery-widget');
		$smarty->registerPlugin(
			'function', 'GALLERY_IMAGE', 'ImageGallery_templateImage'
		);
		$smarty->registerPlugin(
			'function', 'GALLERY_IMAGES', 'ImageGallery_templateImages'
		);
		$smarty->left_delimiter='{{';
		$smarty->right_delimiter='}}';
		$c.=$smarty->fetch($template);
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
		WW_addScript('image-gallery/frontend/gallery.js');
		WW_addInlineScript($script);
		WW_addCSS('/ww.plugins/image-gallery/frontend/gallery.css');
		// }
		return $c;
	}
	else {
		$dir=$vars['directory'];
		return '<em>'
			.__('gallery "%1" not found or empty.', array($dir), 'core')
			.'</em>';
	}
}

// }
