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
  * @link       www.webworks.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id = $_REQUEST['id'];

if (!is_numeric($id)) {
	exit('The supplied id is invalid');
}

dbQuery(
	'update comments set comment = "'.addslashes($_REQUEST['comment']).'"
	where id = '.$id
);

$comment = dbRow('select * from comments where id = '.$id);

echo '{
		"id":'.$id.',
		"comment":"'.$comment['comment'].'"
	}';
