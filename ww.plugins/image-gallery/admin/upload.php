<?php
/**
  * script for uploading images into an image gallery
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

$session_id = @$_POST[ 'PHPSESSID' ];
session_id($session_id);

require '../../../ww.incs/basics.php';

if (!Core_isAdmin()) {
	Core_quit();
}

$gallery_id=(int)@$_POST['gallery_id'];
if (!$gallery_id) {
	Core_quit();
}

$dir=dbOne(
	'select value from page_vars where name="image_gallery_directory"'
	. ' and page_id='.$gallery_id,
	'value'
);
if (!$dir) {
	$dir='image-galleries/imagegallery-'.$gallery_id;
}
$dir=USERBASE.'/f/'.$dir;

if (!file_exists($dir)) {
	@mkdir(USERBASE.'/f/image-galleries'); // parent dir
	mkdir($dir);
}

$position=dbOne(
	'select position from image_gallery where gallery_id=1265'
	.' order by position desc limit 1',
	'position'
);

$dimensions=getimagesize($_FILES['Filedata']['tmp_name']);
$meta=addslashes(
	json_encode(
		array(
			'width'=>$dimensions[0],
			'height'=>$dimensions[1],
			'name'=>$_FILES['Filedata']['name'],
			'caption'=>''
		)
	)
);

$query='insert into image_gallery (gallery_id,position,media,meta) values'
	.'("'.$gallery_id.'","'.($position+1).'","image","'.$meta.'")';
dbQuery($query);
$last_id=dbLastInsertId();

move_uploaded_file(
	$_FILES['Filedata']['tmp_name'],
	$dir.'/'.$_FILES['Filedata']['name']
);

echo $last_id;
Core_quit();
