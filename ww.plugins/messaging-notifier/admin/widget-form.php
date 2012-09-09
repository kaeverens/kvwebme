<?php
/**
	* draw the widget form for the plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}
if (isset($_REQUEST['get_messaging_notifier'])) {
	$id=(int)$_REQUEST['get_messaging_notifier'];
	if ($id) {
		$r=dbRow('select * from messaging_notifier where id='.$id);
	}
	else {
		$r=array('id'=>0, 'messages_to_show'=>10, 'data'=>'[]');
	}
	$r['data']=json_decode($r['data']);
	echo json_encode($r);
	Core_quit();
}
if (@$_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$data=json_decode($_REQUEST['data']);
	foreach ($data as $k=>$r) {
		if ($r->type=='Twitter') {
			$regex='http://twitter.com/statuses/user_timeline/[0-9]*.rss';
			if (!preg_match('#^'.$regex.'$#', $r->url)) {
				$file=file_get_contents($r->url);
				$rss=preg_replace(
					'#.*"('.$regex.')".*#',
					'$1',
					str_replace(array("\n", "\r"), '', $file)
				);
				if (!preg_match('#^'.$regex.'$#', $rss)) {
					unset($data[$k]);
				}
				else {
					$data[$k]->url=$rss;
				}
			}
		}
	}
	$data=addslashes(json_encode($data));
	$sql="messaging_notifier set data='$data'";
	if ($id) {
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else {
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id', 'id');
	}
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	Core_cacheClear('messaging_notifier');
	Core_quit();
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}

echo '<a href="javascript:;" id="messaging_notifier_editlink_'.$id
	.'" class="button messaging_notifier_editlink">view or edit feeds</a><br />';
// { show story title
echo '<strong>hide story title</strong><br />'
	.'<select name="hide_story_title"><option value="0">No</option>'
	.'<option value="1"';
if (@$_REQUEST['hide_story_title']==1) {
	echo ' selected="selected"';
}
echo '>Yes</option></select><br />';
// }
// { characters shown per story
echo '<strong>characters shown</strong><br />';
if (!isset($_REQUEST['characters_shown'])
	|| $_REQUEST['characters_shown']==''
) {
	$_REQUEST['characters_shown']=200;
}
echo '<input class="small" name="characters_shown" value="'
	.((int)$_REQUEST['characters_shown']).'" /><br />';
// }
// { scrolling
echo '<strong>scrolling</strong><br />'
	.'<select name="scrolling"><option value="0">No</option>'
	.'<option value="1"';
if (@$_REQUEST['scrolling']==1) {
	echo ' selected="selected"';
}
echo '>Yes</option></select><br />';
// }
// { load in tab
echo '<strong>load in other tab</strong><br />'
	.'<select name="load_in_other_tab"><option value="1">Yes</option>'
	.'<option value="0"';
if (@$_REQUEST['load_in_other_tab']==0) {
	echo ' selected="selected"';
}
echo '>No</option></select><br />';
// }
// { stories to show
$i=isset($_REQUEST['stories_to_show'])?(int)$_REQUEST['stories_to_show']:10;
if ($i<1) {
	$i=10;
}
echo '<strong>stories to show</strong><br />';
echo '<input class="small" name="stories_to_show" value="'.$i.'" />';
// }
