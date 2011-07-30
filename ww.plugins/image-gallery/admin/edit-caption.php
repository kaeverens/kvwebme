<?php
/**
  * script for editing the caption of an image
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
$caption=addslashes(@$_POST['caption']);

$meta=dbOne('select meta from image_gallery where id='.$id, 'meta');
$meta=json_decode($meta, true);
$meta['caption']=$caption;
$meta=addslashes(json_encode($meta));
dbQuery('update image_gallery set meta="'.$meta.'" where id='.$id);
