<?php
/**
	* poll results
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
$ip=$_SERVER['REMOTE_ADDR'];

header('Content-type: text/json');

$r=dbRow('select * from poll_vote where poll_id='.$id.' and ip="'.$ip.'"');
if (!$r) {
	echo json_encode(
		array(
			'status'=>1,
			'message'=>'You must vote before you can see the results'
		)
	);
	Core_quit();
}
$question=dbOne('select body from poll where id='.$id, 'body');
$html='<div class="question">'.$question.'</div><ul class="answers">';
$answers=dbAll(
	"select *,(select count(ip) from poll_vote where poll_id=$id and n"
	."um=poll_answer.num) as votes from poll_answer where poll_id=$id "
	."order by num"
);
foreach ($answers as $answer) {
	$html.='<li>'.$answer['votes'].': '.htmlspecialchars($answer['answer'])
		.'</li>';
}
$html.='</ul>';
echo json_encode(array('status'=>0, 'html'=>$html));
