<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die("access denied");

$id=(int)$_REQUEST['id'];
$widgets=addslashes($_REQUEST['data']);
dbQuery("update panels set body='$widgets' where id=$id");
Core_cacheClear('panels');
Core_cacheClear('pages');
