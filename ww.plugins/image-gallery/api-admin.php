<?php
/**
  * image gallery admin api
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

function ImageGallery_adminDetailsEdit() {
	$id=(int)@$_REQUEST['id'];
	if (!$id) {
		exit;
	}
	$meta=dbOne('select meta from image_gallery where id='.$id, 'meta');
	$meta=json_decode($meta, true);
	$meta['caption']    = @$_POST['caption'];
	$meta['description']= @$_POST['description'];
	$meta['author']     = @$_POST['author'];
	$meta=json_encode($meta);
	dbQuery('update image_gallery set meta="'.addslashes($meta).'" where id='.$id);
	return array('ok'=>1);
}
function ImageGallery_adminDetailsGet() {
	$id=(int)@$_REQUEST['id'];
	if (!$id) {
		exit;
	}
	$meta=dbOne('select meta from image_gallery where id='.$id, 'meta');
	$meta=json_decode($meta, true);
	return $meta;
}
