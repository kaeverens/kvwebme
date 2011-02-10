<?php
/**
  * Form plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Form
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

$plugin=array(
	'name' => 'Form',
	'admin' => array(
		'page_type' => 'Form_adminPageForm'
	),
	'description' => 'Allows forms to be created so visitors can contact you',
	'frontend' => array(
		'page_type' => 'Form_frontend'
	),
	'version' => 4
);

/**
  * display the form-creation tool
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the form creation tool
  */
function Form_adminPageForm($page, $vars) {
	$edit=$GLOBALS['is_an_update'];
	$id=$page['id'];
	$c='';
	if ($edit) {
		require dirname(__FILE__).'/admin/save.php';
	}
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}

/**
  * display the page's form
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the form's HTML
  */
function Form_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/show.php';
	if (!isset($PAGEDATA->vars['footer'])) {
		$PAGEDATA->vars['footer']='';
	}
	return $PAGEDATA->render()
		.Form_show($PAGEDATA->dbVals, $PAGEDATA->vars)
		.$PAGEDATA->vars['footer'];
}
