<?php
/**
	* ads widget admin
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

echo '<strong>Which Ad Type</strong><br />'
	.'<select name="ad-type">';
$rs=dbAll('select id,name from ads_types order by name');
foreach ($rs as $r) {
	echo '<option value="'.$r['id'].'"';
	if ($r['id']==@$_REQUEST['ad-type']) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars($r['name']).'</option>';
}
echo '</select><br />';
echo '<strong>Amt To Show</strong><br/>'
	.'<input name="how-many"';
if (@$_REQUEST['how-many']) {
	echo ' value="'.((int)$_REQUEST['how-many']).'"';
}
else {
	echo ' value="1"';
}
echo '/><br/>';
