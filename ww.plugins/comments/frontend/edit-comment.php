<?php

/**
  * Updates the comments table
  *
  * PHP Version 5
  *
  * @category   CommentsPlugin
  * @package    WebworksWebme
  * @subpackage CommentsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2.0
  * @link       www.webworks.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id = $_REQUEST['id'];
$comment = $_REQUEST['comment'];

$allowed = in_array($id, $_SESSION['comment_ids']);
if (!$allowed) {
	die('You do not have permission to do this');
}
if (!is_numeric($id)) {
	exit('Invalid id');
}
dbQuery(
	'update comments set comment = "'.addslashes($comment)
	.'" where id = '.(int)$id
);
echo json_encode(array(
	'status'=>1,
	'id'=>$id,
	'comment'=>$comment
));
