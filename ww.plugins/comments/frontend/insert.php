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

$noModeration 
	= dbOne(
		'select value from site_vars where name = "no_moderation"', 'value'
	);
if (is_admin()||$noModeration) {
	$trusted = 1;
}
else {
	$trusted = 0;
}
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
	$_SESSION['comment_ids'][] = $id;
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
	$data=array();
	$data['status']=1;
	$data['id']=$id;
	$data['name']=$name;
	$data['humandate']=$date;
	$data['mysqldate']=$datetime;
	$data['comment']=$comment;
	$data['add']=$addIntroString;
	echo json_encode($data);
}
