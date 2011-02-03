<?php
require '../../ww.incs/basics.php';
if(!is_admin())exit;

$id=(int)$_REQUEST['id'];
$to=(int)$_REQUEST['parent_id'];
$order=explode(',',$_REQUEST['order']);
dbQuery("update pages set parent=$to where id=$id");
for($i=0;$i<count($order);++$i){
	$pid=(int)$order[$i];
	dbQuery("update pages set ord=$i where id=$pid");
	echo "update pages set ord=$i where id=$pid\n";
}
cache_clear('pages');
cache_clear('menus');
dbQuery('update page_summaries set rss=""');
