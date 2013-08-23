<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

// { tag
$tag=isset($_REQUEST['tag'])?$_REQUEST['tag']:'-1';
echo '<label>'.__('Tag')
	.'<br/><select name="tag"><option value="0">'.__('all').'</option>';
$rs=dbAll(
	'select tag,count(entry_id) as entries from blog_tags group by tag'
	.' order by entries desc'
);
foreach ($rs as $r) {
	echo '<option value="'.$r['tag'].'"';
	if ($r['tag']==$tag) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars($r['tag'].' ('.$r['entries'].')').'</option>';
}
echo '</select></label>';
// }
// { widgetType
$widgetType=isset($_REQUEST['widgetType'])?$_REQUEST['widgetType']:'-1';
$widgetTypes=array(
	'Excerpts',
	'Featured Stories'
);
echo '<label>'.__('Type')
	.'<br/><select name="widgetType">';
foreach ($widgetTypes as $k=>$t) {
	echo '<option value="'.$k.'"';
	if ($r['widgetType']==$widgetType) {
		echo ' selected="selected"';
	}
	echo '>'.$t.'</option>';
}
echo '</select></label>';
// }
if ($widgetType==0) { // number of excerpts
	$excerpts=isset($_REQUEST['excerpts'])?(int)$_REQUEST['excerpts']:'0';
	echo '<br/><label>'.__('Number of Excerpts')
		.'<br/><input name="excerpts" type="number" value="'.$excerpts.'"/></label>';
	$excerpts_offset=isset($_REQUEST['excerpts_offset'])?(int)$_REQUEST['excerpts_offset']:'0';
	echo '<br/><label>'.__('Excerpts Offset')
		.'<br/><input name="excerpts_offset" type="number" value="'.$excerpts_offset.'"/></label>';
	$imageSizeX=isset($_REQUEST['imageSizeX'])?(int)$_REQUEST['imageSizeX']:'100';
	$imageSizeY=isset($_REQUEST['imageSizeY'])?(int)$_REQUEST['imageSizeY']:'100';
	echo '<br/>'.__('Image Size')
		.'<br/><input name="imageSizeX" type="number" value="'.$imageSizeX.'" class="small"/>x'
		.'<input name="imageSizeY" type="number" value="'.$imageSizeY.'" class="small"/>';
}
