<?php

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$uri=@$_GET['uri'];
if($uri=='')
	die('no image');

if(strpos($uri,',')!==false){ // width and height
	$uri=explode(',',$uri);
	$width=explode('=',$uri[1]);
	$width=end($width);
	$height=explode('=',$uri[2]);
	$height=end($height);
	$uri=$uri[0];
}

if(strpos($uri,'http://')===false){
	$loc=USERBASE.'f/'.$uri;
	if(!file_exists($loc)){
		$loc=SCRIPTBASE.$uri;
		if(!file_exists($loc))
			exit;
	}
	$uri=$loc;
}

$image=new Image($uri,true);
if(isset($width)&&isset($height))
	$image->resize($width,$height);

$image->display();
?>
