<?php
/**
  * script for re-ordering images in an image gallery
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

$images=@$_POST['image'];
if (count($images)==0) {
	Core_quit();
}

$query='insert into image_gallery (id,position) values ';
foreach ($images as $position=>$id) {
	$query.='('.$id.','.$position.'),';
}
$query=substr($query, 0, -1)
	.' on duplicate key update position=values(position);';
dbQuery($query);
