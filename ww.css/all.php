<?php
require_once '../ww.incs/basics.php';
$files=array(
	'menus.css',
	'ui.datepicker.css',
	'forms.css',
	'contextmenu.css',
	'../j/jquery-ui/css/smoothness/jquery-ui-1.8.1.custom.css'
);
$name='';
if(isset($_REQUEST['extra'])){
	$fs=explode('|',$_REQUEST['extra']);
	foreach($fs as $f){
		if(strpos($f,'..')!==false)continue;
		if(strpos($f,'ww.skins')!==false){
			$fname=USERBASE.'themes-personal'.str_replace('/ww.skins','',$f);
		}
		else $fname=SCRIPTBASE.$f;
		if(!preg_match('/\.css$/',$fname) || !file_exists($fname))continue;
		$files[]=array($fname,$f);
		$name.='|'.$fname;
	}
}

$latest=0;
foreach($files as $f){
	$mt=is_array($f)?filemtime($f[0]):filemtime($f);
	if($mt>$latest)$latest=$mt;
}

$name=md5($name);

if(
	file_exists(USERBASE.'/ww.cache/c/css-'.$name)
	&& filemtime(USERBASE.'/ww.cache/c/css-'.$name)<$latest
){
	unlink(USERBASE.'/ww.cache/c/css-'.$name);
}

$css_code=false; //cache_load('c','css-'.$name);
if($css_code==false){
	$css_code='';
	require 'Minify/CSS.php';
	foreach($files as $f){
		if(is_array($f)){
			$css_code.=Minify_CSS::minify(
				file_get_contents($f[0]),
				array(
					'prependRelativePath'=>preg_replace('/[^\/]*$/','',$f[1])
				)
			);
		}
		else{
			$css_code.=Minify_CSS::minify(
				file_get_contents($f)
			);
		}
	}

	cache_save('c','css-'.$name,$css_code);
}

header('Content-type: text/css; charset=utf-8');
header('Cache-Control: max-age = 2592000');
header('Expires-Active: On');
header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
header('Pragma:');
header('Content-Length: ' . strlen($css_code));

echo $css_code;
