<?php

/**
  * Approves or unapproves a comment
  *
  * PHP Version 5
  *
  * @category   CommentsPlugin
  * @package    WebworksWebme
  * @subpackage CommentsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
**/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!is_admin()) {
	die('You do not have permission to do this');
}
$id = $_REQUEST['id'];
if (!is_numeric($id)) {
	exit ('Invalid id');
}
$val = $_REQUEST['value'];
if ($val==0||$val==1) {
	dbQuery('update comments set isvalid = '.$val.' where id = '.$id);
	echo '{"status":"1", "id":'.$id.', "value":"'.$val.'"}';
}
else {
	echo '{"status":0, "message":"Invalid Value"}';
}
