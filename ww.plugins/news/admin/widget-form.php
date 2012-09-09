<?php
/**
  * News widget admin
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conor MacAoidh <conor@macaoidh.name>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
// { news page
echo '<strong>News Page</strong><br />';
$news_pages=dbAll(
	'select id,name from pages where type="news" or type="news|news" '
	.'order by name'
);
if (!count($news_pages)) {
	echo 'no News pages created. please <a href="/ww.admin/pages.php">create '
		.'one</a> first.';
	Core_quit();
}
echo '<select name="id"><option value=""> -- choose your news page -- '
	.'</option>';
foreach ($news_pages as $b) {
	echo '<option value="'.$b['id'].'"';
	if ($id==$b['id']) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars($b['name']).'</option>';
}
echo '</select><br />';
// }
// { characters shown per stories
echo '<strong>Characters Shown</strong><br />';
if (!isset($_REQUEST['characters_shown'])
	|| $_REQUEST['characters_shown']==''
) {
	$_REQUEST['characters_shown']=200;
}
echo '<input class="small" name="characters_shown" value="'
	.((int)$_REQUEST['characters_shown']).'" /> (0 to only show headline)<br />';
// }
// { scrolling
echo '<strong>Scrolling</strong><br /><select name="scrolling">';
echo '<option value="1">Yes</option>';
echo '<option value="0"';
if ($_REQUEST['scrolling']==0) {
	echo ' selected="selected"';
}
echo '>No</option></select><br />';
// }
// { stories to show
$i=(int)$_REQUEST['stories_to_show'];
if (!$i) {
	$i=10;
}
echo '<strong>Stories to show</strong><br />';
echo '<input class="small" name="stories_to_show" value="'.$i.'" /><br/>';
// }
// { thumbnail
echo '<strong>Show Thumbnail</strong><br /><select name="thumbnail">';
echo '<option value="0">No</option>';
echo '<option value="1"';
if ($_REQUEST['thumbnail']==1) {
	echo ' selected="selected"';
}
echo '>Yes</option></select><br />';
// }
// { thumbnail width/height
$maxw=(int)$_REQUEST['thumbnailw'];
$maxh=(int)$_REQUEST['thumbnailh'];
if ($maxw<1) {
	$maxw=80;
}
if ($maxh<1) {
	$maxh=80;
}
echo '<input class="small" name="thumbnailw" value="'.$maxw.'"/> max width<br />';
echo '<input class="small" name="thumbnailh" value="'.$maxh.'"/> max height<br />';
// }
