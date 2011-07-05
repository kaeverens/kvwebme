<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

$id=(int)@$_POST['id'];
if($id==0)
	exit;

$image=dbRow('select * from image_gallery where id='.$id);
$meta=json_decode($image['meta'],true);
$dir=dbOne(
	'select value from page_vars where name="image_gallery_directory"'
	.'and page_id='.$image['gallery_id']
	,'value'
);
echo '<li id="image_'.$id.'" class="gallery-image-container">';
echo '<img id="image-gallery-image'.$id.'" src="/ww.plugins/image-gallery/get-image.php?uri='.$dir.'/'.$meta['name'].',width=64,height=64""/>';
echo '<a href="javascript:;" class="delete-img" id="'.$id.'">';
echo 'Delete</a><br/>';
echo '<a href="javascript:;" class="edit-img" id="'.$id.'">';
echo 'Add Caption</a>';
echo '</li>';
