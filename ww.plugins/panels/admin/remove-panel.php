<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

if(isset($_REQUEST['id'])){
	$id=(int)$_REQUEST['id'];
	dbQuery("delete from panels where id=$id");
	Core_cacheClear('panels');
}
