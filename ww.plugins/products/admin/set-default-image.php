<?php
/**
	* set default image
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!Core_isAdmin()) {
	die(__('access denied'));
}

$product_id=(int)$_REQUEST['product_id'];
$imgsrc='/'.$_REQUEST['imgsrc'];

dbQuery(
	'update products set image_default="'.addslashes($imgsrc).'"'
	.' where id='.$product_id
);

echo 'ok';
