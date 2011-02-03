<?php
require_once 'initialise.php';

switch($_REQUEST['action']){
	case 'delete_file': // {
		$id=(int)$_REQUEST['id'];
		$file=kfmFile::getInstance($id);
		if($file){
			$file->delete();
			echo 'ok';
			exit;
		}
		else die('file does not exist');
	break; // }
	case 'prune': // {
		global $kfm;
		$root_id = $kfm->setting('root_folder_id');
		$root_directory = kfmDirectory::getInstance($root_id);
		kfm_prune($root_directory);
	break; // }
	case 'change_caption': // {
		$id = $_REQUEST['id'];
		$caption = $_REQUEST['caption'];
		kfm_editCaption($id, $caption);
	break; // }
}

function kfm_prune ($dir) {
	global $root_id;
	if (!$dir->exists()) {
		return $dir->delete();
	}
	$files = $dir->getFiles();
	$subDirs = $dir->getSubdirs();
	if ($dir->hasSubdirs()) {
		foreach ($subDirs as $sub) {
			kfm_prune($sub);
		}
	}
	// { If the directory contains nothing and is not the root delete it
	if (!(count($files)||count($subDirs))&&($dir->id!=$root_id)) {
		return $dir->delete();
	}
	// }
}
function kfm_editCaption ($imgID, $caption) {
	$img = kfmImage::getInstance($imgID);
	$img->setCaption($caption);
	$data['id'] = $imgID;
	$data['caption'] = $caption;
	echo json_encode($data);
}
