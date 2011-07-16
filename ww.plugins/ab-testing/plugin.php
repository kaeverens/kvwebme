<?php
/**
	* kvWebME A/B Testing plugin definition file
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$plugin=array(
	'name' => 'A/B Testing',
	'admin' => array(
		'body_override' => 'ABTesting_admin'
	),
	'frontend' => array(
		'body_override' => 'ABTesting_frontend'
	),
	'triggers'=>array(
		'page-object-loaded'=>'ABTesting_record'
	),
	'description' => 'Provide different page bodies to readers, '
		.'to see which are more successful',
	'version'=>1
);

/**
  * show the page admin tabs
  *
  * @param array $page      the page's db row
	* @param array $page_vars any meta data the page has
  *
  * @return string HTML of the page admin
  */
function ABTesting_admin($page, $page_vars) {
	if ($page === false) {
		require dirname(__FILE__).'/admin/body-rebuild.php';
	}
	else {
		require dirname(__FILE__).'/admin/body-override.php';
	}
	return $body;
}

/**
  * show one version of the page
  *
  * @param array $page the page object
  *
  * @return string HTML of the page content version
  */
function ABTesting_frontend($page) {
	require dirname(__FILE__).'/frontend.php';
	return $body;
}

/**
  * record the page version that resulted in this visit
  *
  * @param array $page the page object
  *
  * @return null
  */
function ABTesting_record($page) {
	require dirname(__FILE__).'/record.php';
}
