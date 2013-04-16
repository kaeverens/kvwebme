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
