<?php
/**
  * script for deleting an image
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

$file=dbRow('select gallery_id,meta from image_gallery where id='.$id);
$dir=dbOne(
	'select value from page_vars where name="image_gallery_directory"'
	.' and page_id='.$file['gallery_id'],
	'value'
);
if ($file==false||$dir==false) {
	Core_quit();
}
$meta=json_decode($file['meta'], true);
if (file_exists(USERBASE.'/f/'.$dir.'/'.$meta['name'])) {
	unlink(USERBASE.'/f'.$dir.'/'.$meta['name']);
}

dbQuery('delete from image_gallery where id='.$id);
