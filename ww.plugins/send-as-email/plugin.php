<?php
/**
  * Send-As-Email plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage SendAsEmail
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       http://kvweb.me/
 */

$plugin=array(
	'name' => 'Send As Email',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/send-as-email/admin/widget.php',
			'js_include' => '/ww.plugins/send-as-email/admin/widget.js'
		)
	),
	'description' => 'Send the currently viewed page as an email',
	'frontend' => array(
		'widget' => 'SendAsEmail_widget'
	)
);

/**
  * display the page
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the plugin's HTML
  */
function SendAsEmail_widget($vars) {
	include_once SCRIPTBASE.'ww.plugins/send-as-email/frontend/widget.php';
	return SendAsEmail_showWidget($vars);
}
