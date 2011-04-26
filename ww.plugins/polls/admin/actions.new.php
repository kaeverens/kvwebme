<?php
dbQuery("insert into poll set name='".addslashes($_REQUEST['name'])."',body='".addslashes($_REQUEST['body'])."',enabled=".(int)$_REQUEST['enabled']);
$id=dbOne('select last_insert_id() as id','id');
$answers=$_REQUEST['answers'];
for($i=0;$i<count($answers);++$i){
	$answer=$answers[$i];
	dbQuery("insert into poll_answer set poll_id=$id,num=$i,answer='".addslashes($answer)."'");
}
echo '<em>Poll created</em>';
cache_clear('polls');
