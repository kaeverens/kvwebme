<?php
$session_id = @$_POST[ 'PHPSESSID' ];
session_id( $session_id );

require '../../../ww.incs/basics.php';

if(!is_admin())
	exit;

$gallery_id=(int)@$_POST['gallery_id'];
if($gallery_id==0)
	exit;

$dir=dbOne('select value from page_vars where name="image_gallery_directory"'
	. ' and page_id='.$gallery_id,'value');

$position=dbOne('select position from image_gallery where gallery_id=1265'
.' order by position desc limit 1','position');

$dimensions=getimagesize($_FILES['file_upload']['tmp_name']);
$meta=addslashes(json_encode(array(
	'width'=>$dimensions[0],
	'height'=>$dimensions[1],
	'name'=>$_FILES['file_upload']['name'],
	'caption'=>''
)));

$query='insert into image_gallery (gallery_id,position,media,meta) values';
$query.='("'.$gallery_id.'","'.($position+1).'","image","'.$meta.'")';
dbQuery($query);
$last_id=dbLastInsertId();

move_uploaded_file(
	$_FILES['file_upload']['tmp_name'],
	USERBASE.'f'.$dir.'/'.$_FILES['file_upload']['name']
);

echo $last_id;
exit;
?>
