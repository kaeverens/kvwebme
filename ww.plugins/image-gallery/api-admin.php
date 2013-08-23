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

// {

/**
	* ImageGallery_adminDetailsEdit
	*
	* @return status
	*/
function ImageGallery_adminDetailsEdit() {
	$id=(int)@$_REQUEST['id'];
	if (!$id) {
		Core_quit();
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

// }
// { ImageGallery_adminDetailsGet

/**
	* ImageGallery_adminDetailsGet
	* get details of a gallery
	*
	* @return details of the gallery
	*/
function ImageGallery_adminDetailsGet() {
	$id=(int)@$_REQUEST['id'];
	if (!$id) {
		Core_quit();
	}
	$meta=dbOne('select meta from image_gallery where id='.$id, 'meta');
	$meta=json_decode($meta, true);
	return $meta;
}

// }
// { ImageGallery_adminFrameUpload

/**
	* upload a frame to the gallery
	*
	* @return status
	*/
function ImageGallery_adminFrameUpload() {
	$gallery_id=(int)$_REQUEST['id'];
	if (!$gallery_id) {
		return array(
			'error'=>__('No gallery ID sent')
		);
	}
	$dir=USERBASE.'/f/image-galleries';
	@mkdir($dir);
	$from=$_FILES['Filedata']['tmp_name'];
	$to  =$dir.'/frame-'.$gallery_id.'.png';
	`convert $from $to`;
	return array('done'=>1);
}

// }
// { ImageGallery_adminAddVideo

/**
	* add a video to a gallery
	*
	* @return null
	*/
function ImageGallery_adminAddVideo() {
	$id=(int)@$_REQUEST['id'];
	$link=@$_REQUEST['link'];
	$image=@$_REQUEST['image'];
	if ($id==0||$link=='') {
		Core_quit(__('ID or Link is missing'));
	}
	if ($image=='http://') {
		$image='';
	}
	$meta=json_encode(
		array(
			'href'=>$link,
			'image'=>$image,
		)
	);
	$query='insert into image_gallery (gallery_id,position,media,meta) values '
		.'('.$id.',"9999","video","'.addslashes($meta).'")';
	dbQuery($query);
}

// }
