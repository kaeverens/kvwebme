<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

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
`rm -rf $dir`;
mkdir($dir);

$ubase=USERBASE;
$fdir=USERBASE.'f';
$tdir=USERBASE.'themes-personal';

`cd $ubase  && zip -r $dir/files.zip f`;

$theme=$DBVARS['theme'];
`cd $ubase  && zip -r $dir/theme.zip themes-personal/$theme`;

$data=array();
$tables=dbAll('show tables');
foreach($tables as $table){
	foreach($table as $k=>$v){
		$data[$v]=dbAll('select * from `'.$v.'`');
	}
}
file_put_contents($dir.'/db.json',json_encode($data));

require CONFIG_FILE;
unset($DBVARS['username']);
unset($DBVARS['password']);
unset($DBVARS['hostname']);
unset($DBVARS['db_name']);
unset($DBVARS['userbase']);
unset($DBVARS['theme_dir']);
unset($DBVARS['theme_dir_personal']);
file_put_contents($dir.'/config.json',json_encode($DBVARS));

$sname=$_SERVER['HTTP_HOST'].date('-Y-m-d').'.zip';
`cd $tmpdir && zip -r -P "$password" $sname site`;

header('Content-type: force/download');
header('Content-Disposition: attachment; filename="'.$sname.'"');
readfile($tmpdir.'/'.$sname);
`rm -rf $tmpdir`;
