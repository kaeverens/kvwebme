<?php

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';

$page_id=(int)@$_GET['id'];
if($page_id==0)
	exit;

$image_dir=@$_POST['image_gallery_directory'];
if($image_dir!=''&&is_dir(USERBASE.'f'.$image_dir)){ // load from kfm for stuff not in database
	$dir=preg_replace('/^\//','',$image_dir);
	$dir_id=kfm_api_getDirectoryID($dir);
	$images=kfm_loadFiles($dir_id);
	$n=count($images);
	if($n==0)
		die('none');
	$f=array();
	foreach($images['files'] as $file){
		array_push($f,array(
			'id'=>$file['id'],
			'name'=>$file['name'],
			'width'=>$file['width'],
			'media'=>'image',
			'height'=>$file['height'],
			'caption'=>$file['caption'],
			'url'=>'/kfmget/'.$file['id']
		));
	}
}
else{ // fall back to reading from database
	$files=dbAll(
		'select * from image_gallery where gallery_id='
		.$page_id.' order by position asc'
	);
	$dir=dbOne(
		'select value from page_vars where name="image_gallery_directory"'
		.'and page_id='.$page_id
		,'value'
	);
	$f=array();
	foreach($files as $file){
		$meta=json_decode($file['meta'],true);
		switch($file['media']){
			case 'image':
				array_push($f,array(
					'id'=>$file['id'],
					'name'=>$meta['name'],
					'media'=>'image',
					'width'=>$meta['width'],
					'height'=>$meta['height'],
					'caption'=>$meta['caption'],
					'url'=>'/ww.plugins/image-gallery/get-image.php?uri='.$dir.'/'.$meta['name']
				));
			break;
			case 'video':
				$image=($meta['image']=='')?
					'/ww.plugins/image-gallery/files/video.png':
					$meta['image'];
				array_push($f,array(
					'id'=>$file['id'],
					'media'=>'video',
					'image'=>'/ww.plugins/image-gallery/get-image.php?uri='.$image,
					'href'=>$meta['href']
				));
			break;
		}
	}
}
die(json_encode($f));
?>
