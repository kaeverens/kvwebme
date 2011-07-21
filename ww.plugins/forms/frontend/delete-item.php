<?php
/**
  * delete an uploaded file
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

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$id=@$_POST['id'];
if ($id==''||strpos('..', $id)!==false) {
	exit;
}

$dir=USERBASE.'f/.files/forms/'.session_id().'/';
if (!is_dir($dir)) {
	exit;
}
$dir.=$id;
@unlink($dir);
