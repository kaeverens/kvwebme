<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

$images=@$_POST['image'];
if(count($images)==0)
	exit;

$query='insert into image_gallery (id,position) values ';
foreach($images as $position=>$id){
	$query.='('.$id.','.$position.'),';
}
$query=substr($query,0,-1);
$query.=' on duplicate key update position=values(position);';
dbQuery($query);
