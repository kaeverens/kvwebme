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
	die(__('access denied'));
}
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.admin/admin_libs.php';

$password=addslashes($_REQUEST['password']);
if (!$password) {
	Core_quit();
}
$tmpdir='/tmp/cmsBackup-'.md5($_SERVER['HTTP_HOST'].microtime(true));

mkdir($tmpdir);
if (!is_dir($tmpdir)) {
	die(__('Could not create tmp directory %1', array($tmpdir), 'core'));
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

$tables=dbAll('show tables');
mkdir($dir.'/db');
foreach ($tables as $table) {
	foreach ($table as $k=>$v) {
		mkdir($dir.'/db/'.$v);
		$count=dbOne('select count(*) as cnt from '.$v, 'cnt');
		for ($i=0;$i<$count;$i+=100) {
			$data=dbAll('select * from `'.$v.'` limit '.$i.', 100');
			file_put_contents(
				$dir.'/db/'.$v.'/'.($i/100).'.json', json_encode($data)
			);
		}
	}
}

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
