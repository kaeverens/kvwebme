
<?php
/**
  * forms api
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conor MacAoidh <conor.macaoidh@gmail.com>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

/**
	* delete an uploaded file
	*
	* @return null
	*/
function Forms_fileDelete() {
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
}
