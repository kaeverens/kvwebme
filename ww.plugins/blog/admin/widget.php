<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

$tag=isset($_REQUEST['tag'])?$_REQUEST['tag']:'-1';
echo '<label>'.__('Category')
	.'<select name="tag"><option value="0">'.__('all').'</option>';
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
