<?php

$session_id = @$_POST[ 'PHPSESSID' ];
session_id( $session_id );

require '../../../ww.incs/basics.php';

$dir=USERBASE.'f/.files/forms/';
if(!is_dir($dir)){ // make forms dir
	mkdir($dir);
}
$dir.=$session_id.'/';
if(!is_dir($dir)){ // make dir named after $session_id
	mkdir($dir);
}
// { make sure too many files aren't being uploaded
$size=WW_Directory::getSize($dir);
if($size>52428800){ // greater than 50mb
	WW_Directory::delete($dir);
	echo 'deleted';
	exit;
}
// }
move_uploaded_file(
	$_FILES['file-upload']['tmp_name'],
	$dir.$_FILES['file-upload']['name']
);
echo 'upload';
exit;
?>
