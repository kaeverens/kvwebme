<?php
/**
  * Deletes a comment
  *
  * PHP Version 5.3
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
$allowed = is_admin()||in_array($id, $_SESSION['comment_ids']);
if (!$allowed) {
	die('You do not have permission to delete this comment');
}
if (!is_numeric($id)) {
	exit('Invalid id');
}
dbQuery('delete from comments where id = '.$id);
if (dbOne('select id from comments where id  = '.$id, 'id')) {
	echo '{"status":0}';
}
else {
	echo '{"status":1, "id":'.$id.'}';
}
