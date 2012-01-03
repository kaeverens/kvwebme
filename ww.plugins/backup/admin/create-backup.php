<?php
/**
	* Backup plugin backup script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.admin/admin_libs.php';

$password=addslashes($_REQUEST['password']);
if (!$password) {
	exit;
}
$tmpdir='/tmp/webmeBackup-'.md5($_SERVER['HTTP_HOST'].microtime(true));

mkdir($tmpdir);
if (!is_dir($tmpdir)) {
	die('couldn\'t create tmp directory '.$tmpdir);
}

$dir=$tmpdir.'/site';
CoreDirectory::delete($dir);
mkdir($dir);

$ubase=USERBASE;
$fdir=USERBASE.'/f';
$tdir=USERBASE.'/themes-personal';

`cd $ubase  && zip -r $dir/files.zip f`;

$theme=$DBVARS['theme'];
`cd $ubase  && zip -r $dir/theme.zip themes-personal/$theme`;

$data=array();
$tables=dbAll('show tables');
foreach ($tables as $table) {
	foreach ($table as $k=>$v) {
		$data[$v]=dbAll('select * from `'.$v.'`');
	}
}
file_put_contents($dir.'/db.json', json_encode($data));

require CONFIG_FILE;
unset($DBVARS['username']);
unset($DBVARS['password']);
unset($DBVARS['hostname']);
unset($DBVARS['db_name']);
unset($DBVARS['userbase']);
unset($DBVARS['theme_dir']);
unset($DBVARS['theme_dir_personal']);
file_put_contents($dir.'/config.json', json_encode($DBVARS));

$sname=$_SERVER['HTTP_HOST'].date('-Y-m-d').'.zip';
`cd $tmpdir && zip -r -P "$password" $sname site`;

header('Content-type: force/download');
header('Content-Disposition: attachment; filename="'.$sname.'"');
readfile($tmpdir.'/'.$sname);
CoreDirectory::delete($tmpdir);
