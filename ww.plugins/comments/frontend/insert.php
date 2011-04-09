<?php

/**
  * Puts a comment into the database
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

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/common.php';

$name = $_REQUEST['name'];
$email = $_REQUEST['email'];
$site = isset($_REQUEST['site'])?$_REQUEST['site']:'';
$page = $_REQUEST['page'];
$comment = $_REQUEST['comment'];

$trusted=($is_admin
	|| dbOne('select value from site_vars where name="no_moderation"', 'value')
)?1:0;
if (!is_numeric($page)) {
	echo '{"status":0, "message":"The page id should be a number"}';
}
elseif (!dbOne('select id from pages where id = '.$page, 'id')) {
	echo '{"status":0, "message":"No page with that id exists"}';
}
else {
	dbQuery(
		'insert into comments set
		name = "'.addslashes($name).'",
		email = "'.addslashes($email).'",
		objectid = '.$page.',
		isvalid = '.$trusted.',
		cdate = now(),
		comment = "'.addslashes($comment).'",
		homepage ="'.addslashes($site).'"'
	);
	$id = dbOne('select last_insert_id() as id', 'id');
//	$_SESSION['comment_ids'][] = $id; // turning this off to avoid confusion...
	if (isset($DBVARS['comments_moderatorEmail']) && $DBVARS['comments_moderatorEmail']) {
		mail(
			$DBVARS['comments_moderatorEmail'],
			'['.$_SERVER['HTTP_HOST'].'] new comment',
			addslashes($name)." has commented on your site:\n".addslashes($comment)."\n\nTo approve or delete this comment, please log into your administration area and go to Communication>Comments",
			'From: noreply@'.$_SERVER['HTTP_HOST']."\nReply-to: noreply@".$_SERVER['HTTP_HOST']
		);
	}
	$count 
		= dbOne(
			'select count(id) from comments 
			where objectid = '.$page, 
			'count(id)'
		);
	$datetime = dbOne('select cdate from comments where id = '.$id, 'cdate');
	$date = date_m2h($datetime);
	if ($count>1) {
		$addIntroString=0;
	}
	else {
		$addIntroString=1;
	}
	$data=array(
		'status'=>1,
		'id'    =>$id,
		'name'  =>$name,
		'humandate'=>$date,
		'mysqldate'=>$datetime,
		'comment'=>$comment,
		'add'   =>$addIntroString,
		'moderated'=>$trusted?0:1
	);
	echo json_encode($data);
}
