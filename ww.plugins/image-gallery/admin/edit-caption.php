<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

$id=(int)@$_POST['id'];
if($id==0)
	exit;
$caption=addslashes(@$_POST['caption']);

$meta=dbOne('select meta from image_gallery where id='.$id,'meta');
$meta=json_decode($meta,true);
$meta['caption']=$caption;
$meta=addslashes(json_encode($meta));
dbQuery('update image_gallery set meta="'.$meta.'" where id='.$id);
