<?php
/**
  * copy a template
  *
  * PHP Version 5.3
  *
  * @category   None
  * @package    None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       http://webme.kvsites.ie/
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die("access denied");
}

$from=$_REQUEST['from'];
$to  =$_REQUEST['to'];

$errors=array();
if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $from) !== '') {
	$errors[]='invalid "From" name';
}
if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $to) !== '') {
	$errors[]='invalid "To" name';
}

$to.='.html';
$from.='.html';
$d=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h');
$from_found=false;
foreach ($d as $f) {
	if ($f->isDot()) {
		continue;
	}
	$fn=$f->getFileName();
	if ($fn==$to) {
		$errors[]='that template already exists';
	}
	if ($fn==$from) {
		$from_found=true;
	}
}
if (!$from_found) {
	$errors[]='the "From" template does not exist';
}

if (!count($errors)) {
	copy(THEME_DIR.'/'.THEME.'/h/'.$from, THEME_DIR.'/'.THEME.'/h/'.$to);
	if (!file_exists(THEME_DIR.'/'.THEME.'/h/'.$to)) {
		$errors[]='failed to copy the file. please check file permissions';
	}
}

if (count($errors)) {
	echo json_encode(array(
		'error'=>join("\n", $errors)
	));
}
else {
	echo json_encode(array('success'=>1));
}
