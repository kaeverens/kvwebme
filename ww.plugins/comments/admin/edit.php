<?php

/**
  * Edits a review
  *
  * PHP Version 5
  *
  * @category   CommentsPlugin
  * @package    WebworksWebme
  * @subpackage CommentsPlugin
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id = $_REQUEST['id'];

if (!is_numeric($id)) {
	Core_quit('The supplied id is invalid');
}

dbQuery(
	'update comments set comment="'.addslashes($_REQUEST['comment']).'" where id='
	.$id
);
Core_cacheClear('comments');
$comment = dbRow('select * from comments where id = '.$id);
echo json_encode(
	array(
		'id'=>$id,
		'comment'=>$comment
	)
);
