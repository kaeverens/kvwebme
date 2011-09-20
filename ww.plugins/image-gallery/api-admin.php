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
function ImageGallery_adminFrameUpload() {
	$gallery_id=(int)@$_POST['id'];
	if (!$gallery_id) {
		exit;
	}
	$dir=USERBASE.'f/image-galleries';
	@mkdir($dir);
	$from=$_FILES['file_upload']['tmp_name'];
	$to  =$dir.'/frame-'.$gallery_id.'.png';
	`convert $from $to`;
	return array('done'=>1);
}
