<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('You do not have permission to delete a forum');
}
$id = $_REQUEST['id'];
if (!(is_numeric($id))) {
	exit('Invalid id');
}
$data = array();
$threads = dbAll('select id from forums_threads where forum_id = '.$id);
$postsToDelete = array();
foreach ($threads as $thread) {
	$thread_id = $thread['id'];
	$posts = dbAll('select id from forums_posts where thread_id = '.$thread_id);
	foreach ($posts as $post) {
		$postsToDelete[] = $post['id'];
	}
	dbQuery('delete from forums_posts where thread_id = '.$thread_id);
	dbQuery('delete from forums_threads where id = '.$thread_id);
}
dbQuery('delete from forums where id = '.$id);
if (dbOne('select id from forums where id = '.$id, 'id')) {
	$data['status'] = 0;
	$data['message'] = 'Could not delete this forum';
}
else {
	$data['status'] = 1;
	$data['id'] = $id;
	$data['posts'] = $postsToDelete;
}
echo json_encode($data);
