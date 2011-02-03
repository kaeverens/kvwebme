<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(isset($_REQUEST['id']) && isset($_REQUEST['pages'])){
	$id=(int)$_REQUEST['id'];
	$json='['.addslashes($_REQUEST['pages']).']';
	dbQuery("update panels set visibility='$json' where id=$id");
	cache_clear('panels');
}
