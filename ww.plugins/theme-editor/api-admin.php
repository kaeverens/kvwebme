<?php
/**
	* theme editor admin api
	*
	* PHP Version 5.3
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL Version 2
	* @link     http://webme.kvsites.ie/
	*/

// { ThemeEditor_adminTemplateCopy

/**
	* copy a template file
	*
	* @return status
	*/
function ThemeEditor_adminTemplateCopy() {
	$from=$_REQUEST['from'];
	$to  =$_REQUEST['to'];
	$errors=array();
	if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $from) !== '') {
		$errors[]=__('invalid "From" name');
	}
	if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $to) !== '') {
		$errors[]=__('invalid "To" name');
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
			$errors[]=__('that template already exists');
		}
		if ($fn==$from) {
			$from_found=true;
		}
	}
	if (!$from_found) {
		$errors[]=__('the "From" template does not exist');
	}
	if (!count($errors)) {
		copy(THEME_DIR.'/'.THEME.'/h/'.$from, THEME_DIR.'/'.THEME.'/h/'.$to);
		if (!file_exists(THEME_DIR.'/'.THEME.'/h/'.$to)) {
			$errors[]=__('failed to copy the file. please check file permissions');
		}
	}
	if (count($errors)) {
		return array(
			'error'=>join("\n", $errors)
		);
	}
	return array('success'=>1);
}

// }
// { ThemeEditor_adminCssCopy

/**
	* copy a CSS file
	*
	* @return status
	*/
function ThemeEditor_adminCssCopy() {
	$from=$_REQUEST['from'];
	$to  =$_REQUEST['to'];
	$errors=array();
	if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $from) !== '') {
		$errors[]=__('invalid "From" name');
	}
	if (preg_replace('/[a-zA-Z0-9\-_ ]/', '', $to) !== '') {
		$errors[]=__('invalid "To" name');
	}
	$to.='.css';
	$from.='.css';
	$d=new DirectoryIterator(THEME_DIR.'/'.THEME.'/c');
	$from_found=false;
	foreach ($d as $f) {
		if ($f->isDot()) {
			continue;
		}
		$fn=$f->getFileName();
		if ($fn==$to) {
			$errors[]=__('that CSS file already exists');
		}
		if ($fn==$from) {
			$from_found=true;
		}
	}
	if (!$from_found) {
		$errors[]=__('the "From" file does not exist');
	}
	if (!count($errors)) {
		copy(THEME_DIR.'/'.THEME.'/c/'.$from, THEME_DIR.'/'.THEME.'/c/'.$to);
		if (!file_exists(THEME_DIR.'/'.THEME.'/c/'.$to)) {
			$errors[]=__('failed to copy the file. please check file permissions');
		}
	}
	if (count($errors)) {
		return array(
			'error'=>join("\n", $errors)
		);
	}
	return array('success'=>1);
}

// }
