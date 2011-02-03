<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(isset($_REQUEST['id']))$id=(int)$_REQUEST['id'];
else $id=0;

// { news page
echo '<strong>News Page</strong><br />';
$news_pages=dbAll('select id,name from pages where type="news" order by name');
if(!count($news_pages)){
	echo 'no News pages created. please <a href="/ww.admin/pages.php">create one</a> first.';
	exit;
}
echo '<select name="id"><option value=""> -- choose your news page -- </option>';
foreach($news_pages as $b){
	echo '<option value="'.$b['id'].'"';
	if($id==$b['id'])echo ' selected="selected"';
	echo '>'.htmlspecialchars($b['name']).'</option>';
}
echo '</select><br />';
// }
// { characters shown per stories
echo '<strong>Characters Shown</strong><br />';
if(!isset($_REQUEST['characters_shown']) || $_REQUEST['characters_shown']=='')$_REQUEST['characters_shown']=200;
echo '<input class="small" name="characters_shown" value="'.((int)$_REQUEST['characters_shown']).'" /> (0 to only show headline)<br />';
// }
// { scrolling
echo '<strong>Scrolling</strong><br /><select name="scrolling">';
echo '<option value="1">Yes</option>';
echo '<option value="0"';
if($_REQUEST['scrolling']==0) echo ' selected="selected"';
echo '>No</option></select><br />';
// }
// { stories to show
$i=(int)$_REQUEST['stories_to_show'];
if(!$i)$i=10;
echo '<strong>Stories to show</strong><br />';
echo '<input class="small" name="stories_to_show" value="'.$i.'" />';
// }
