<?php

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id=@$_POST['id'];
if($id==''||strpos('..',$id)!==false)
	exit;

$dir=USERBASE.'f/.files/forms/'.session_id().'/';
if(!is_dir($dir))
	exit;
$dir.=$id;
if(!file_exists($dir))
	exit;
unlink($dir);
?>
