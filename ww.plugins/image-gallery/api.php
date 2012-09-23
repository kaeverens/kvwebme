<?php
/**
  * script for retrieving a JSON array of images/videos in a gallery
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

// { ImageGallery_galleryGet

/**
  * script for retrieving a JSON array of images/videos in a gallery
	*
	* @return array gallery details
	*/
function ImageGallery_galleryGet() {
	$page_id=(int)@$_REQUEST['id'];
	if ($page_id==0) {
		Core_quit();
	}
	$image_dir=@$_REQUEST['image_gallery_directory'];
	if ($image_dir!=''&&is_dir(USERBASE.'/f'.$image_dir)) { // read from KFM
		$dir=preg_replace('/^\//', '', $image_dir);
		$dir_id=kfm_api_getDirectoryID($dir);
		$images=kfm_loadFiles($dir_id);
		$n=count($images);
		if ($n==0) {
			die('none');
		}
		$f=array();
		foreach ($images['files'] as $file) {
			array_push(
				$f,
				array(
					'id'=>$file['id'],
					'name'=>$file['name'],
					'width'=>$file['width'],
					'media'=>'image',
					'height'=>$file['height'],
					'caption'=>$file['caption'],
					'url'=>'/kfmget/'.$file['id']
				)
			);
		}
	}
	else { // fall back to reading from database
		$files=dbAll(
			'select * from image_gallery where gallery_id='
			.$page_id.' order by position asc'
		);
		$dir=dbOne(
			'select value from page_vars where name="image_gallery_directory"'
			.'and page_id='.$page_id,
			'value'
		);
		if ($dir===false) {
			$dir='/image-galleries/imagegallery-'.$page_id;
		}
		$f=array();
		foreach ($files as $file) {
			$meta=json_decode($file['meta'], true);
			switch ($file['media']) {
				case 'image': // {
					$arr=array(
						'id'=>$file['id'],
						'name'=>$meta['name'],
						'media'=>'image',
						'width'=>$meta['width'],
						'height'=>$meta['height'],
						'url'=>'/a/f=getImg/'.$dir.'/'.$meta['name']
					);
					if (@$meta['author']) {
						$arr['author']=$meta['author'];
					}
					if (@$meta['caption']) {
						$arr['caption']=$meta['caption'];
					}
					if (@$meta['description']) {
						$arr['description']=$meta['description'];
					}
					$f[]=$arr;
				break; // }
				case 'video': // {
					$image=($meta['image']=='')?
						'/ww.plugins/image-gallery/files/video.png':
						$meta['image'];
					array_push(
						$f,
						array(
							'id'=>$file['id'],
							'media'=>'video',
							'url'=>'/a/f=getImg/'.$image,
							'href'=>$meta['href']
						)
					);
				break; // }
			}
		}
	}
	// { get gallery data
	$rs=dbAll(
		'select * from page_vars where page_id='.$page_id
		.' and name like "image_gallery_%"',
		'name'
	);
	// }
	return array(
		'items'=>$f,
		'caption-in-slider'=>(int)@$rs['image_gallery_captions_in_slider']['value'],
		'image-width'=>(int)@$rs['image_gallery_image_x']['value'],
		'image-height'=>(int)@$rs['image_gallery_image_y']['value'],
		'frame'=>array(
			'type'=>@$rs['image_gallery_frame']['value'],
			'padding'=>@$rs['image_gallery_frame_custom_padding']['value'],
			'border'=>@$rs['image_gallery_frame_custom_border']['value']
		)
	);
}

// }
// { ImageGallery_img

/**
	* retrieve an image from the database
	*
	* @return null
	*/
function ImageGallery_img() {
	$id    =(int)$_REQUEST['id'];
	$width =@(int)$_REQUEST['w'];
	$height=@(int)$_REQUEST['h'];
	$sql='select * from image_gallery where id='.$id;
	$r=dbRow($sql);
	$meta=json_decode($r['meta']);
	$url='/a/f=getImg/w='.$width.'/h='.$height.'/image-galleries/'
		.'imagegallery-'.$r['gallery_id'].'/'.$meta->name;
	header('Location: '.$url);
	Core_quit();
}

// }
// { ImageGallery_frameGet

/**
	* get a frame for images
	*
	* @return null
	*/
function ImageGallery_frameGet() {
	if (isset($_REQUEST['ratio'])) {
		$ratio=(float)$_REQUEST['ratio'];
	}
	else {
		$ratio=1;
	}
	$padding=explode(' ', $_REQUEST['pa']);
	$border=explode(' ', $_REQUEST['bo']);
	$width=$_REQUEST['w']+($padding[1]+$padding[3])/$ratio;
	$height=$_REQUEST['h']+($padding[0]+$padding[2])/$ratio;
	$file=USERBASE.'/f/'.$_REQUEST['_remainder'];
	if (strpos($file, '/.')!==false) {
		Core_quit();
	}
	if (!file_exists($file)) {
		header('Location: /i/blank.gif');
		Core_quit();
	}
	$md5=md5($_SERVER['REQUEST_URI']);
	$frame=USERBASE.'/ww.cache/image-gallery-frames/frame-'.$md5.'.png';
	if (!file_exists($frame)) {
		@mkdir(USERBASE.'/ww.cache/image-gallery-frames');
		$imgO=imagecreatefrompng($file);
		if ($img0===false) { // not a PNG
			header('Location: /i/blank.gif');
			Core_quit();
		}
		$imgOsize=getimagesize($file);
		$imgN=imagecreatetruecolor($width, $height);
		$black = imagecolorallocate($imgN, 0, 0, 0);
		imagecolortransparent($imgN, $black);
		// top left 
		imagecopyresampled(
			$imgN, $imgO,
			0, 0, 0, 0,
			ceil($border[3]/$ratio), ceil($border[0]/$ratio), $border[3], $border[0]
		);
		// top right 
		imagecopyresampled(
			$imgN, $imgO,
			$width-floor($border[1]/$ratio)-1, 0, $imgOsize[0]-$border[1]-1, 0,
			ceil($border[1]/$ratio), ceil($border[0]/$ratio), $border[1], $border[0]
		);
		// bottom left 
		imagecopyresampled(
			$imgN, $imgO,
			0, $height-floor($border[2]/$ratio)-1, 0, $imgOsize[1]-$border[2]-1,
			ceil($border[3]/$ratio), ceil($border[2]/$ratio), $border[3], $border[2]
		);
		// bottom right 
		imagecopyresampled(
			$imgN, $imgO,
			$width-floor($border[1]/$ratio)-1, $height-floor($border[2]/$ratio)-1,
			$imgOsize[0]-$border[1]-1, $imgOsize[1]-$border[2]-1,
			ceil($border[1]/$ratio), ceil($border[2]/$ratio), $border[1], $border[2]
		);
		// left
		imagecopyresampled(
			$imgN, $imgO,
			0, floor($border[0]/$ratio), 0, $border[0],
			ceil($border[3]/$ratio), $height-floor(($border[2]+$border[0])/$ratio),
			$border[3], $imgOsize[1]-$border[2]-$border[0]
		);
		// right
		imagecopyresampled(
			$imgN, $imgO,
			$width-floor($border[1]/$ratio)-1, floor($border[0]/$ratio),
			$imgOsize[0]-$border[1]-1, $border[0],
			ceil($border[1]/$ratio), $height-floor(($border[2]+$border[0])/$ratio),
			$border[3], $imgOsize[1]-$border[2]-$border[0]
		);
		// top
		imagecopyresampled(
			$imgN, $imgO,
			floor($border[3]/$ratio), 0, $border[3], 0,
			$width-floor(($border[3]+$border[1])/$ratio), ceil($border[0]/$ratio),
			$imgOsize[0]-$border[3]-$border[1], $border[0]
		);
		// bottom
		imagecopyresampled(
			$imgN, $imgO,
			floor($border[3]/$ratio), $height-floor($border[2]/$ratio)-1,
			$border[3], $imgOsize[1]-$border[2]-1,
			$width-floor(($border[3]+$border[1])/$ratio), ceil($border[2]/$ratio),
			$imgOsize[0]-$border[3]-$border[1], $border[2]
		);
	}
	header('Content-type: image/png');
	imagepng($imgN, $frame);
	header('Cache-Control: max-age=2592000, public');
	header('Expires-Active: On');
	header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
	header('Pragma:');
	header('Content-Length: ' . filesize($frame));
	readfile($frame);
}

// }
