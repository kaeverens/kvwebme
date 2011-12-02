<?php

/**
	* Checks if the captcha is correct
	*
	* PHP Version 5.3
	*
	* @category   CommentsPlugin
	* @package    WebworksWebme
	* @subpackage CommentsPlugin
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/recaptcha.php';
$result=recaptcha_check_answer(
	RECAPTCHA_PRIVATE,
	$_SERVER['REMOTE_ADDR'],
	$_REQUEST['challenge'],
	$_REQUEST['response']
);
if ($result->is_valid) {
	echo '{"status":1}';
}
else {
	echo '{"status":0}';
}
