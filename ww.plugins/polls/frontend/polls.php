<?php
function poll_display() {
	WW_addScript('polls');
	$poll=dbRow('select * from poll where enabled limit 1');
	if (!count($poll)) {
		return '<div class="polls-wrapper"><em>No polls available.</em></div>';
	}
	$id=$poll['id'];
	$html='<div class="polls-wrapper" poll-id="'.$id.'">';

	$html.='<div class="question">'.$poll['body'].'</div><ul class="answers">';
	$answers=dbAll(
		"select * from poll_answer where poll_id=$id order by num"
	);
	foreach ($answers as $answer) {
		$html.='<li><input type="radio" name="poll_answer" value='
			.'"'.$answer['num'].'" />'.htmlspecialchars($answer['answer']).'</li>';
	}
	$html.='</ul><input type="button" class="polls-vote" value="Vote" /><inpu'
		.'t type="button" class="polls-results" value="View Results" />';

	$html.='</div>';
	return $html;
}
