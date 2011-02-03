<?php
function poll_display(){
	$action=$_REQUEST['poll_action'];
	if(!$action){
		$md5=md5('view questions');
		$cache=cache_load('polls',$md5);
		if($cache)return $cache;
	}
	$poll=dbRow('select * from poll where enabled limit 1');
	if(!count($poll))return '<div class="poll"><em>No polls available.</em></div>';
	$id=$poll['id'];
	$html="<div class='poll' id='poll_$id'>";
	if($action=='Vote')$html.=poll_vote($id,$poll);
	else if($action=='View Results')return poll_get_results($id,$poll);
	else $html.=poll_get_html($id,$poll);
	$html.='</div>';
	if(!$action){
		cache_save('polls',$md5,$html);
	}
	return $html;
}
function poll_get_html($poll_id,$poll){
	$html='<div class="question">'.$poll['body'].'</div><form action='.$GLOBALS['PAGEDATA']->getRelativeURL().' method="post"><ul class="answers">';
	$answers=dbAll("select * from poll_answer where poll_id=$poll_id order by num");
	foreach($answers as $answer){
		$html.='<li><input type="radio" name="poll_answer['.$poll_id.']" value="'.$answer['num'].'" />'.htmlspecialchars($answer['answer']).'</li>';
	}
	$html.='</ul><input type="submit" name="poll_action" value="Vote" /><input type="submit" name="poll_action" value="View Results" /></form>';
	return $html;
}
function poll_get_results($poll_id,$poll){
	$html='<div class="question">'.$poll['body'].'</div><ul class="answers">';
	$answers=dbAll("select *,(select count(ip) from poll_vote where poll_id=$poll_id and num=poll_answer.num) as votes from poll_answer where poll_id=$poll_id order by num");
	foreach($answers as $answer){
		$html.='<li>'.$answer['votes'].': '.htmlspecialchars($answer['answer']).'</li>';
	}
	$html.='</ul>';
	return $html;
}
function poll_vote($poll_id,$poll){
	$num=(int)$_REQUEST['poll_answer'];
	if(!$num)return '<em>Please choose an option.</em>'.poll_get_html($poll_id,$poll);
	$ip=$_SERVER['REMOTE_ADDR'];
	$r=dbRow("select * from poll_vote where poll_id=$poll_id and ip='$ip'");
	if(count($r))return '<em>You\'ve already voted in this poll.</em>'.poll_get_html($poll_id,$poll);
	dbQuery("insert into poll_vote set poll_id=$poll_id,ip='$ip',num=$num");
	return '<em>Thank you. Your vote has been recorded.</em>'.poll_get_results($poll_id,$poll);
}
