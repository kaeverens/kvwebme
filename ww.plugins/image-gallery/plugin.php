<?php
/**
  * ImageGallery plugin definition file
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

// { config
$plugin=array(
	'name' =>function() {
		return __('Image Gallery');
	},
	'version'=>3,
	'admin' => array(
		'page_type' => 'ImageGallery_adminPageForm',
		'widget' => array(
			'form_url'   => '/ww.plugins/image-gallery/admin/widget-form.php',
			'js_include' => '/ww.plugins/image-gallery/admin/widget.js'
		)
	),
	'description' =>function() {
		return __('Allows a directory of images to be shown as a gallery.');
	},
	'frontend' => array(
		'page_type' => 'ImageGallery_frontend',
		'widget' => 'ImageGallery_widget'
	)
);
// }

// { ImageGallery_adminPageForm

/**
  * administration area for the image gallery
  *
  * @param array $page Page database row
	* @param array $vars meta data for the page
  *
  * @return string HTML of the administration area
  */
function ImageGallery_adminPageForm($page, $vars) {
	require_once dirname(__FILE__).'/admin/index.php';
	return $c;
}

// }
// { ImageGallery_frontend

/**
  * show the image gallery
  *
  * @param object $PAGEDATA the page object
  *
  * @return string HTML of the image gallery
  */
function ImageGallery_frontend($PAGEDATA) {
	require_once dirname(__FILE__).'/frontend/show.php';
	return ImageGallery_show($PAGEDATA);
}

// }
// { ImageGallery_widget

/**
	* image gallery widget
	*
	* @param array $vars parameters
	*
	* @return html
	*/
function ImageGallery_widget($vars=null) {
	require_once dirname(__FILE__).'/frontend/show.php';
	return GalleryWidget_show($vars);
}

// }
