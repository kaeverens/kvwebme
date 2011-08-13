<?php

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
		exit('Invalid id');
	}
	dbQuery(
		'update comments set comment = "'.addslashes($comment)
		.'" where id = '.(int)$id
	);
	return array('status'=>1, 'id'=>$id, 'comment'=>$comment);
}
