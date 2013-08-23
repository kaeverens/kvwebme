<?php
if (!isset($_REQUEST['areyousure']) || $_REQUEST['areyousure']!='yes') {
	echo __('This script deletes any files older than 6 months. in order to ensure it's not accidentally run by bots such as search engines, you must add ?areyousure=yes to the address of this script to run it.');
	Core_quit();
}
require_once '../ww.incs/basics.php';
if (!Core_isAdmin()) {
	Core_quit();
}

$saved=0;
$deleted=0;
function find_old_files($dirname, $before_date) {
	$dir=new DirectoryIterator($dirname);
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		if ($file->getFilename()=='kfm') {
			continue;
		}
		$fname=$dirname.'/'.$file->getFilename();
		if (is_dir($fname)) {
			find_old_files($fname, $before_date);
		}
		else {
			$delete=fileatime($fname)<$before_date;
			if (!$delete) {
				continue;
			}
			$GLOBALS['deleted']++;
			$GLOBALS['saved']+=filesize($fname);
			echo date('Y-m-d', fileatime($fname)).' '.$fname.'<br />';
			unlink($fname);
		}
	}
}
find_old_files(USERBASE.'/f/.files', time()-3600*24*182);
echo __('%1 files deleted.', array($deleted), 'core').'<br />';
echo __('%1 bytes saved.', array($saved), 'core');
