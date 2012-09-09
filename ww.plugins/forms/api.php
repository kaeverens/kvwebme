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
		Core_quit();
	}
	$dir=USERBASE.'/f/.files/forms/'.session_id().'/';
	if (!is_dir($dir)) {
		Core_quit();
	}
	$dir.=$id;
	@unlink($dir);
}

/**
	* send a random code to an email address to verify it
	*
	* @ return array saying it happened
	*/
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
		$pid=(int)@$_REQUEST['page'];
		if ($pid) {
			$page=Page::getInstance($pid);
			if (!$page) {
				return array('error'=>'page not found');
			}
			$page->initValues();
			$prevent=(int)@$page->vars['forms_preventUserFromSubmitting'];
			if ($prevent) {
				$id=(int)dbOne(
					'select id from user_accounts where email="'.addslashes($email).'"',
					'id'
				);
				if ($id) {
					if ($prevent==1) { // don't allow any users to submit
						return array(
							'error'=>$page->vars['forms_preventUserFromSubmittingMessage']
						);
					}
					if ($prevent<4) { // parse conditions
						$user=User::getInstance($id);
						if ($user) {
							$cond_val=$page->vars['forms_preventUserFromSubmittingCondVal'];
							$cond_key=$page->vars['forms_preventUserFromSubmittingCondKey'];
							if (($prevent==3 && $user->get($cond_key) == $cond_val)
								|| ($prevent==2 && $user->get($cond_key) != $cond_val)
							) {
								return array(
									'error'=>$page->vars['forms_preventUserFromSubmittingMessage']
								);
							}
						}
					}
				}
			}
		}
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

/**
	* check that an email address was verified, or provide a code to verify it
	*
	* @return array the status of the email
	*/
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
