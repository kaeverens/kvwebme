<?php
/**
	* user-get-location project
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name'            => 'User - Get Location',
	'hide_from_admin' => true,
	'admin' => array(
		'page_type' => 'User_getLocation_admin'
	),
	'frontend' => array(
		'page_type' => 'User_getLocation_frontend'
	),
	'triggers'=>array(
		'page-object-loaded'=>'User_getLocation_checkUserLocation'
	),
	'version'=>1
);
// }

/**
  * get the location
  *
  * @param array $PAGEDATA page object
  *
  * @return string page html
  */
function User_getLocation_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/page.php';
	return $PAGEDATA->render()
		.User_getLocation_frontendShow($PAGEDATA->dbVals, $PAGEDATA->vars)
		.$PAGEDATA->vars['footer'];
}

/**
  * display the map thing
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the map thing
  */
function User_getLocation_admin($page, $vars) {
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}

/**
  * if the user doesn't have location data set, make sure to set it
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string stuff
  */
function User_getLocation_checkUserLocation($PAGEDATA) {
	if (!isset($_SESSION['userdata'])
		|| $_SESSION['userdata']['latitude']
	) {
		return;
	}
	if ($PAGEDATA->type!='user-location') {
		redirect('/_r?type=user-location');
	}
}
