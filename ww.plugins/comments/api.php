<?php
/**
	* API for Comments plugin
	*
	* PHP Version 5
	*
	* @category   CommentsPlugin
	* @package    KVWebme
	* @subpackage CommentsPlugin
	* @author     Kae Verens <kae@kvsites.ie>
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @license    GPL Version 2.0
	* @link       www.kvweb.me
	**/

/**
	* Update the comments table
	*
	* @return null
	*/
function Comments_update() {
	$id = $_REQUEST['id'];
	$comment = $_REQUEST['comment'];
	$allowed = in_array($id, $_SESSION['comment_ids']);
	if (!$allowed) {
		die('You do not have permission to do this');
	}
	if (!is_numeric($id)) {
		Core_quit('Invalid id');
	}
	dbQuery(
		'update comments set comment = "'.addslashes($comment)
		.'" where id = '.(int)$id
	);
	Core_cacheClear('comments');
	return array('status'=>1, 'id'=>$id, 'comment'=>$comment);
}
