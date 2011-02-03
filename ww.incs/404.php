<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$r=preg_replace('/^\//','',$_SERVER['REQUEST_URI']);
if(preg_match('#^f/.*\.[A-Z]{3}\.([^.]*)$#',$r)){
	echo 'missing image';exit;
}
$r=preg_replace('/\?.*/','',$r);
$r=addslashes(urldecode($r));
if(strlen($r)>1 && strlen($r)-1==strrpos($r,'/')){ // tried to access a page as a directory
	header('Location: /'.preg_replace('/\/$/','',$r));
	exit;
}
$d=Page::getInstanceByName($r);
if($d && isset($d->id) && $d->id){
	$id=$d->id;
	header('Location: '.$d->getRelativeURL());
}
else{
	header('HTTP/1.0 404 Not Found');
	echo '<h1>File not found</h1><p>The requested file '
		.'<code>'.htmlspecialchars($_SERVER['REQUEST_URI']).'</code>'
		.' does not exist on this server.</p>';
}
