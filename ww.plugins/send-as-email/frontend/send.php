<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!isset($PLUGINS['send-as-email'])) {
	die('this plugin is not installed');
}

// { validate email
$to=$_REQUEST['rcp'];
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
	die("invalid To email address");
}
$from=$_REQUEST['sdr'];
if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
	die("invalid From email address");
}
// }

// { validate url
$url=$_REQUEST['url'];
if (strpos($url, '&__t=') !==false || strpos($url, '?__t=') !==false) {
	$url=preg_replace('/[\?\&]__t=[^\&]*/', '', $url);
}
$url.='&__t='.$_REQUEST['tpl'];
if (strpos($url, '&') !== false && strpos($url, '?') === false) {
	$url=preg_replace('/&/', '?', $url, 1);
}
// }

$subject=$_REQUEST['sub'];

$f=file_get_contents($url);
$f=preg_replace('#(src=|href=)"/#', '\1"http://'.$_SERVER['HTTP_HOST'].'/', $f);
preg_match_all('#<link[^<>]*href="([^"]*)"[^>]*>#', $f, $matches);
foreach ($matches[1] as $k=>$match) {
	$styles=file_get_contents($match);
	$f=str_replace($matches[0][$k], '<style>'.$styles.'</style>', $f);
}

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/mail.php';
send_mail($to, $from, $subject, $f);
echo 'sent';
