<?php
require_once '../ww.incs/basics.php';
require_once '../ww.incs/jslibs.php';

header('Cache-Control: max-age=2592000, public');
header('Expires-Active: On');
header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
header('Pragma:');
header('Content-type: text/javascript;'); // charset=utf-8');

$files=array(
	'jquery.json-2.2.min.js',
	'js.js'
);
if(isset($_REQUEST['extra'])){
	$fs=explode('|',$_REQUEST['extra']);
	array_shift($fs);
	foreach($fs as $f){
		if(strpos($f,'..')!==false)continue;
		$fname=SCRIPTBASE.$f;
		if(!preg_match('/\.js$/',$fname) || !file_exists($fname))continue;
		$files[]=$fname;
	}
}

$latest=0;
foreach($files as $f){
	$mt=filemtime($f);
	if($mt>$latest)$latest=$mt;
}
$mt=filemtime(__FILE__);
if($mt>$latest)$latest=$mt;

$name=md5(join('|',$files));

if(
	file_exists(USERBASE.'/ww.cache/j/js-'.$name)
	&& filemtime(USERBASE.'/ww.cache/j/js-'.$name)<$latest
){
	unlink(USERBASE.'/ww.cache/j/js-'.$name);
}

$js=cache_load('j','js-'.$name);
if($js==false){
	$js='';
	foreach($files as $f){
		$js.=file_get_contents($f).';';
	}
	cache_save('j','js-'.$name,$js);
}
echo $js;
