<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

$id=(int)@$_POST['id'];
$link=@$_POST['link'];
$image=@$_POST['image'];
if($id==0||$link=='')
	exit;

if($image=='http://')
	$image='';

$meta=addslashes(json_encode(array(
	'href'=>$link,
	'image'=>$image,
)));

$query='insert into image_gallery (gallery_id,position,media,meta) values ';
$query.='('.$id.',"9999","video","'.$meta.'")';
dbQuery($query);
