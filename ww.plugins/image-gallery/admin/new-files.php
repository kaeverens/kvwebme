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
	die("access denied");
}

$id=(int)@$_POST['id'];
if ($id==0) {
	exit;
}

$image=dbRow('select * from image_gallery where id='.$id);
$meta=json_decode($image['meta'], true);
$dir=dbOne(
	'select value from page_vars where name="image_gallery_directory"'
	.'and page_id='.$image['gallery_id'],
	'value'
);
echo '<li id="image_'.$id.'">';
echo '<img id="image-gallery-image'.$id.'" src="/a/f=getImg/w=64/h=64/'
	.$dir.'/'.$meta['name'].'"/>';
echo '<a href="javascript:;" class="edit-img" id="'.$id.'">Edit</a> or ';
echo '<a href="javascript:;" class="delete-img" id="'.$id.'">[x]</a>';
echo '</li>';
