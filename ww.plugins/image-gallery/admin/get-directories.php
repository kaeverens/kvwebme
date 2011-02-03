<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

function get_subdirs($base,$dir){
	$arr=array();
	$D=new DirectoryIterator($base.$dir);
	$ds=array();
	foreach($D as $dname){
		$d=$dname.'';
		if($d{0}=='.')continue;
		if(!is_dir($base.$dir.'/'.$d))continue;
		$ds[]=$d;
	}
	asort($ds);
	foreach($ds as $d){
		$arr[]=$dir.'/'.$d;
		$arr=array_merge($arr,get_subdirs($base,$dir.'/'.$d));
	}
	return $arr;
}

$arr=array_merge(array('/'),get_subdirs(USERBASE.'f',''));
foreach($arr as $d){
	echo '<option value="',htmlspecialchars($d),'"';
	if($_REQUEST['selected']==$r['directory'])echo ' selected="selected"';
	echo '>',htmlspecialchars($d),'</option>';
}
