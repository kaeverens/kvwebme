<?php
/**
  * Recommend-This-Site plugin definition file
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage RecommendThisSite
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$plugin=array(
	'name' => 'Recommend This Site',
	'admin' => array(
		'page_type' => 'RecommendThisSite_adminPageForm'
	),
	'description' => 'Let visitors send an email to a friend about your site',
	'frontend' => array(
		'page_type' => 'RecommendThisSite_frontend'
	)
);

/**
  * display the admin page form
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the form
  */
function RecommendThisSite_adminPageForm($page, $vars) {
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}

/**
  * display the recommend-this-site page
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the plugin's HTML
  */
function RecommendThisSite_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/show.php';
	if (!isset($PAGEDATA->vars['footer'])) {
		$PAGEDATA->vars['footer']='';
	}
	return $PAGEDATA->render()
		.RecommendThisSite_show($PAGEDATA->dbVals, $PAGEDATA->vars)
		.$PAGEDATA->vars['footer'];
}
