
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
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>'invalid email address');
	}
	if (!isset($_SESSION['emails'])) {
		$_SESSION['emails']=array();
	}
	if (!isset($_SESSION['emails'][$email])) {
		$_SESSION['emails'][$email]=rand(10000,99999);
	}
	mail(
		$email,
		'['.$_SERVER['HTTP_HOST'].'] email verification code',
		'The verification code for this email address is: '
		.$_SESSION['emails'][$email]
	);
	return array('ok'=>1);
}
function Forms_emailVerify() {
	if (!isset($_REQUEST['email'])) {
		return array('error'=>'no email parameter');
	}
	$email=$_REQUEST['email'];
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return array('error'=>'invalid email address');
	}
	if (!isset($_SESSION['emails']) || !isset($_SESSION['emails'][$email])) {
		return array('error'=>'session expired, or email not entered');
	}
	if ($_SESSION['emails'][$email]===true
		|| @(int)$_REQUEST['code']===$_SESSION['emails'][$email]
	) {
		$_SESSION['emails'][$email]=true;
		return array('ok'=>1);
	}
	return array('error'=>'incorrect code');
}
