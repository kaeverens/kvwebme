<?php
/**
  * script for showing all items in a gallery
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
if ($id==0) {
	Core_quit();
}

$image=dbRow('select * from image_gallery where id='.$id);
$meta=json_decode($image['meta'], true);
$dir=dbOne(
	'select value from page_vars where name="image_gallery_directory"'
	.'and page_id='.$image['gallery_id'],
	'value'
);
if (!$dir) {
	$dir='/image-galleries/imagegallery-'.$image['gallery_id'];
	dbQuery(
		'insert into page_vars set name="image_gallery_directory",value="'
		.addslashes($dir).'",page_id='.$image['gallery_id']
	);
}
echo '<li id="image_'.$id.'">';
echo '<img id="image-gallery-image'.$id.'" src="/a/f=getImg/w=64/h=64/'
	.$dir.'/'.$meta['name'].'"/>';
echo '<a href="javascript:;" class="edit-img" id="'.$id.'">'.__('Edit')
	.'</a> '.__('or').' ';
echo '<a href="javascript:;" class="delete-img" id="'.$id.'">'.__('[x]').'</a>';
echo '</li>';
