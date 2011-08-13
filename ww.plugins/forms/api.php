
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
	$id=@$_REQUEST['id'];
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
function Forms_verificationSend() {
	if (!isset($_REQUEST['email'])) {
		return array('error'=>'no email parameter');
	}
	if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
		return array('error'=>'invalid email address');
	}
	if (!isset($_REQUEST['name'])) {
		return array('error'=>'no name parameter');
	}
	if (!isset($_SESSION['form_input_email_verify_'.$_REQUEST['name']])) {
		return array('error'=>'session has expired - please reload and try again');
	}
	mail(
		$_REQUEST['email'],
		'['.$_SERVER['HTTP_HOST'].'] email verification code',
		'The verification code for this email address is: '
		.$_SESSION['form_input_email_verify_'.$_REQUEST['name']]
	);
	return array('ok'=>1);
}
