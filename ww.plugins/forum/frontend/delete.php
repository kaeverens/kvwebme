<?php
/**
  * delete a message from a forum
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Forum
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
	exit;
}

$post_id=(int)$_REQUEST['id'];

$errs=array();

if (!$post_id) {
	$errs[]='no post selected';
}
if (!is_admin()
	&& dbOne(
		'select author_id from forums_posts where id='.$post_id,
		'author_id'
	) != $_SESSION['userdata']['id']
) {
	$errs[]='this is not your post, or post does not exist';
}

if (count($errs)) {
	echo json_encode(
		array(
			'errors'=>$errs
		)
	);
	exit;
}

dbQuery('delete from forums_posts where id='.$post_id);
dbQuery(
	'update forums_threads set num_posts='
	.'(select count(id) as ids from forums_posts '
	.'where thread_id=forums_threads.id)'
);

echo json_encode(array(
	'ok'=>1
));
