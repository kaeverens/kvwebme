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
	* @link       www.kvweb.me
	**/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id = $_REQUEST['id'];
$allowed = Core_isAdmin()||in_array($id, $_SESSION['comment_ids']);
if (!$allowed) {
	die('You do not have permission to delete this comment');
}
if (!is_numeric($id)) {
	Core_quit('Invalid id');
}
dbQuery('delete from comments where id = '.$id);
Core_cacheClear('comments');
if (dbOne('select id from comments where id  = '.$id, 'id')) {
	echo '{"status":0}';
}
else {
	echo '{"status":1, "id":'.$id.'}';
}
