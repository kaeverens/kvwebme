<?php
$translation=0;
echo '<h3>'.($id?__('Edit Poll'):__('New Poll')).'</h3>';
if($id){
	$data=dbRow('select * from poll where id='.$id);
}
else $data=array('name'=>'','enabled'=>1,'body'=>'');
echo '<form action="'.$_url.'" method="post">';
if($id)echo '<input type="hidden" name="id" value="'.$id.'" />';
echo '<div id="poll-tabs">';
echo '<ul>';
echo '<li><a href="#polls-main-details-tab">Main</a></li>';
echo '<li><a href="#polls-answers-tab">Answers</a></li>';
echo '</ul>';
// { main details
	echo '<div id="polls-main-details-tab">';
	echo '<table class="poll_creation_table" style="width:100%">';
	echo '<tr><th>'.__('Name').'</th><td><input name="name" value="'.htmlspecialchars($data['name']).'" /></td>';
	echo '<th>'.__('Enabled').'</th><td><select name="enabled"><option value="1">Yes</option><option value="0"';
	if($data['enabled']==0)echo ' selected="selected"';
	echo '">No</option></select></td></tr>';
	echo '<tr><th>Question</th><td colspan="3">',ckeditor('body',$data['body']),'</td></tr>';
	echo '</table></div>';
// }
// { answers
	echo '<div id="polls-answers-tab"><table id="poll_answers" width="100%">';
	echo '<tr><th>Answer</th><th>Votes so far</th><th><a href="javascript:add_answer_row()">add answer</a></th></tr>';
	if($id){
		$answers=dbAll("select * from poll_answer where poll_id=$id order by num");
		foreach($answers as $answer){
			echo '<tr><td><input class="large" name="answers[]" value="'.htmlspecialchars($answer['answer']).'" /></td><td colspan="2">todo</td></tr>';
		}
	}
	echo '<tr><td><input class="large" name="answers[]" /></td><td colspan="2">&nbsp;</td></tr>';
	echo '</table>';
	echo '<script type="text/javascript">function add_answer_row(){$("#poll_answers > tbody").append("<tr><td><input class=\"large\" name=\"answers[]\" /></td><td colspan=\"2\">&nbsp;</td></tr>");}</script>';
	echo '</div>';
// }
echo '</div><input type="submit" name="action" value="'.($id?__('Edit Poll'):__('Create Poll')).'" /></form>';
