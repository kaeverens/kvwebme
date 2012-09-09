<?php
/**
  * script for adding a new video to an image gallery
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

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

$id=(int)@$_POST['id'];
$link=@$_POST['link'];
$image=@$_POST['image'];
if ($id==0||$link=='') {
	Core_quit();
}

if ($image=='http://') {
	$image='';
}

$meta=addslashes(
	json_encode(
		array(
			'href'=>$link,
			'image'=>$image,
		)
	)
);

$query='insert into image_gallery (gallery_id,position,media,meta) values '
	.'('.$id.',"9999","video","'.$meta.'")';
dbQuery($query);
