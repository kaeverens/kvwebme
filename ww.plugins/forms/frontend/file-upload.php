<?php
/**
  * file uploads for Forms
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$session_id = @$_POST[ 'PHPSESSID' ];
session_id($session_id);

require '../../../ww.incs/basics.php';

$dir=USERBASE.'/f/.files/forms/';
if (!is_dir($dir)) { // make forms dir
	mkdir($dir);
}
$dir.=$session_id.'/';
if (!is_dir($dir)) { // make dir named after $session_id
	mkdir($dir);
}
// { make sure too many files aren't being uploaded
$size=CoreDirectory::getSize($dir);
if ($size>52428800) { // greater than 50mb
	CoreDirectory::delete($dir);
	Core_quit(__('Deleted'));
}
// }
if (isset($_FILES['file-upload'])) {
	move_uploaded_file(
		$_FILES['file-upload']['tmp_name'],
		$dir.$_FILES['file-upload']['name']
	);
}
echo __('Upload');
