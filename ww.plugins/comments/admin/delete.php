<?php

/**
  * A delete script for comments
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

if (!is_admin()) {
	die('You do not have permission to delete comments');
}
if (!is_numeric($id)) {
	exit;
}

dbQuery('delete from comments where id = '.(int)$id);

if (dbOne('select id from comments where id = '.$id, 'id')) {
	echo '{"status":0}';
}
else {
	echo '{"status":1, "id":'.$id.'}';
}
