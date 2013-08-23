<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

if(isset($_REQUEST['id']) && isset($_REQUEST['disabled'])){
	$id=(int)$_REQUEST['id'];
	$disabled=(int)$_REQUEST['disabled'];
	dbQuery("update panels set disabled='$disabled' where id=$id");
	Core_cacheClear('panels');
}
echo __('done');
