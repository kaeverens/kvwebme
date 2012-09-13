<?php
/**
	* vote
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id=(int)$_REQUEST['id'];
$vote=(int)$_REQUEST['vote'];
$ip=$_SERVER['REMOTE_ADDR'];

header('Content-type: text/json');

$r=dbRow('select * from poll_vote where poll_id='.$id.' and ip="'.$ip.'"');
if ($r) {
	echo json_encode(
		array(
			'status'=>1,
			'message'=>'You have already voted in this poll'
		)
	);
	Core_quit();
}
dbQuery('insert into poll_vote set poll_id='.$id.',ip="'.$ip.'",num='.$vote);
echo json_encode(array('status'=>0));
