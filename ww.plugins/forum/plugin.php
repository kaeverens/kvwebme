<?php
/**
  * Forum plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Forum
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

$plugin=array(
	'name' => 'Forum',
	'admin' => array(
		'page_type' => 'Forum_adminPageForm'
	),
	'description' => 'Add a forum to let your readers talk to each other',
	'frontend' => array(
		'page_type' => 'Forum_frontend'
	),
	'version' => 5
);

/**
  * display the forum-creation tool
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the forum creation tool
  */
function Forum_adminPageForm($page, $vars) {
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}

/**
  * display the page's forum
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the forum's HTML
  */
function Forum_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/forum.php';
	return Forum_show($PAGEDATA);
}
