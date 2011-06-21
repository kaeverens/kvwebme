<?php

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';

$page_id=(int)@$_GET['id'];
if($page_id==0)
	exit;

$vars=dbRow('select value from page_vars where page_id='
	.$page_id.' and name="image_gallery_directory"');
if(!$vars)
	exit;
$image_dir=$vars['value'];
$dir=preg_replace('/^\//','',$image_dir);
$dir_id=kfm_api_getDirectoryID($dir);
$images=kfm_loadFiles($dir_id);
$n=count($images);
if($n==0)
	die('none');
die(json_encode($images));

?>
