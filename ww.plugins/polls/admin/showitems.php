<?php
echo '<h3>Existing Polls</h3>';
if(!$start)$start=0;
$polls=Polls::getAll(false);
$end=count($polls);
if(count($polls)){
	// {
	$navlinks='';
	if($start){
		$navlinks.='|<a href="'.$_url.'&amp;action=showPolls&amp;start='.($start>99?$start-100:0).'">Prev</a>| ';
	}
	if($start+100<=$end){
		$navlinks.='|<a href="'.$_url.'&amp;action=showPolls&amp;start='.($start+100).'">Next</a>| ';
	}
	// }
	echo '<table><tr>';
	echo '<th>Name</th>';
	echo '<th>Enabled</th><th>'.$navlinks.'</th></tr>';
	$polls=array_slice($polls,$start,100);
	foreach($polls as $r){
		echo '<tr>';
		echo '<td>'.htmlspecialchars($r->name).'</td>';
		echo '<td>'.($r->enabled?'Yes':'No').'</td>';
		echo '<td><a href="'.$_url.'&amp;action=editPoll&amp;id='.$r->id.'&amp;start='.$start.'">edit</a>, <a href="'.$_url.'&amp;action=deletePoll&amp;id='.$r->id.'&amp;start='.$start.'" onclick="return confirm(\'are you sure you want to delete this poll?\')">[x]</a></td></tr>';
	}
	echo '<tr><td>&nbsp;</td><td>&nbsp;</td><td>'.$navlinks.'</td></tr>';
	echo '</table>';
}else{
	echo '<em>none yet</em>';
}
