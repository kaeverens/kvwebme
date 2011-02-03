<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}
if (isset($_REQUEST['get_messaging_notifier'])) {
	$id=(int)$_REQUEST['get_messaging_notifier'];
	if($id)$r=dbRow('select * from messaging_notifier where id='.$id);
	else $r=array('id'=>0,'messages_to_show'=>10,'data'=>'[]');
	$r['data']=json_decode($r['data']);
	echo json_encode($r);
	exit;
}
if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$data=json_decode($_REQUEST['data']);
	foreach($data as $k=>$r){
		switch($r->type){
			case 'Twitter': // {
				if(!preg_match('#^http://twitter.com/statuses/user_timeline/[0-9]*.rss$#',$r->url)){
					$file=file_get_contents($r->url);
					$rss=preg_replace('#.*"(http://twitter.com/statuses/user_timeline/[0-9]*.rss)".*#','$1',str_replace(array("\n","\r"),'',$file));
					if(!preg_match('#^http://twitter.com/statuses/user_timeline/[0-9]*.rss$#',$rss))unset($data[$k]);
					else $data[$k]->url=$rss;
				}
			// }
		}
	}
	$data=addslashes(json_encode($data));
	$sql="messaging_notifier set data='$data'";
	if($id){
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else{
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id','id');
	}
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	cache_clear('messaging_notifier');
	exit;
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}

echo '<a href="javascript:;" id="messaging_notifier_editlink_'.$id.'" class="button messaging_notifier_editlink">view or edit feeds</a><br />';
// { show story title
echo '<strong>hide story title</strong><br /><select name="hide_story_title">';
echo '<option value="0">No</option>';
echo '<option value="1"';
if(isset($_REQUEST['hide_story_title']) && $_REQUEST['hide_story_title']==1) echo ' selected="selected"';
echo '>Yes</option></select><br />';
// }
// { characters shown per story
echo '<strong>characters shown</strong><br />';
if(!isset($_REQUEST['characters_shown']) || $_REQUEST['characters_shown']=='')$_REQUEST['characters_shown']=200;
echo '<input class="small" name="characters_shown" value="'.((int)$_REQUEST['characters_shown']).'" /><br />';
// }
// { scrolling
echo '<strong>scrolling</strong><br /><select name="scrolling">';
echo '<option value="0">No</option>';
echo '<option value="1"';
if(isset($_REQUEST['scrolling']) && $_REQUEST['scrolling']==1) echo ' selected="selected"';
echo '>Yes</option></select><br />';
// }
// { load in tab
echo '<strong>load in other tab</strong><br /><select name="load_in_other_tab">';
echo '<option value="1">Yes</option>';
echo '<option value="0"';
if(isset($_REQUEST['load_in_other_tab']) && $_REQUEST['load_in_other_tab']==0) echo ' selected="selected"';
echo '>No</option></select><br />';
// }
// { stories to show
$i=isset($_REQUEST['stories_to_show'])?(int)$_REQUEST['stories_to_show']:10;
if($i<1)$i=10;
echo '<strong>stories to show</strong><br />';
echo '<input class="small" name="stories_to_show" value="'.$i.'" />';
// }
