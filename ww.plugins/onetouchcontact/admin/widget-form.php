<?php
/**
	* widget admin for OneTouchContact widget
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
if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
// { client id
echo '<strong>client id</strong><br />';
if (!isset($_REQUEST['cid']) || $_REQUEST['cid']=='') {
	$_REQUEST['cid']=0;
}
echo '<input class="small" name="cid" value="'
	.((int)$_REQUEST['cid']).'" /><br />';
// }
// { mailinglist id
echo '<strong>mailinglist id</strong><br />';
if (!isset($_REQUEST['mid']) || $_REQUEST['mid']=='') {
	$_REQUEST['mid']=0;
}
echo '<input class="small" name="mid" value="'.((int)$_REQUEST['mid'])
	.'" /><br />';
// }
// { ask for phone number
echo '<strong>ask for phone</strong><br /><select name="phone">';
echo '<option value="0">No</option>';
echo '<option value="1"';
if (isset($_REQUEST['phone']) && $_REQUEST['phone']==1) {
	echo ' selected="selected"';
}
echo '>Yes</option></select><br />';
// }
