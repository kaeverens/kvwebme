<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

if (isset($_REQUEST['id']) && isset($_REQUEST['vis']) && isset($_REQUEST['hid'])) {
	$id=(int)$_REQUEST['id'];
	$vis='['.addslashes($_REQUEST['vis']).']';
	$hid='['.addslashes($_REQUEST['hid']).']';
	dbQuery("update panels set visibility='$vis',hidden='$hid' where id=$id");
	Core_cacheClear('panels');
}
