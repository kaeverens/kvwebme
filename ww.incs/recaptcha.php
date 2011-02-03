<?php
/**
	* redirect the browser to an appropriate page (for logins, shops, etc)
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     http://webworks.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT']
	.'/ww.incs/recaptcha-php-1.11/recaptchalib.php';
define('RECAPTCHA_PRIVATE', '6LffZAwAAAAAANXRgBLgD51o6fZnvXknLNNXgCUr');
define('RECAPTCHA_PUBLIC', '6LffZAwAAAAAALA70eSDf73p4DTddBu0jgULjukb'); 

/**
  * retrieve HTML for a captcha
  *
  * @return string HTML for the captcha
  */
function Recaptcha_getHTML() {
	return '<script>var RecaptchaOptions={theme:"custom",lang:"en",'
		.'custom_theme_widget:"recaptcha_widget"};</script>'
		.'<div id="recaptcha_widget" style="display:none">'
		.'<div id="recaptcha_image"></div>'
		.'<a href="javascript:Recaptcha.reload()">reload captcha</a><br />'
		.'<div class="recaptcha_only_if_incorrect_sol" style="color:red">'
		.'Incorrect please try again</div><span class="recaptcha_only_if_image">'
		.'Enter the words above:</span>'
		.'<input id="recaptcha_response_field" '
		.'name="recaptcha_response_field" /></div>'
		.'<script src="http://www.google.com/recaptcha/api/challenge?k='
		.RECAPTCHA_PUBLIC.'"></script>';
}
