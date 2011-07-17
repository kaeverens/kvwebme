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

$plugin=array(
	'name' => 'Image Gallery',
	'version'=>2,
	'admin' => array(
		'page_type' => 'ImageGallery_adminPageForm'
	),
	'description' => 'Allows a directory of images to be shown as a gallery.',
	'frontend' => array(
		'page_type' => 'ImageGallery_frontend'
	)
);

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
