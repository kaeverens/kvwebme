<?php
dbQuery("update poll set name='".addslashes($_REQUEST['name'])."',body='".addslashes($_REQUEST['body'])."',enabled=".(int)$_REQUEST['enabled']." where id=$id");
$answers=$_REQUEST['answers'];
dbQuery("delete from poll_answer where poll_id=$id");
$num=0;
foreach($answers as $answer){
	if(!$answer)continue;
	$num++;
	dbQuery("insert into poll_answer set poll_id=$id,num=$num,answer='".addslashes($answer)."'");
}
echo '<em>'.__('Poll updated').'</em>';
cache_clear('polls');
