<?php
/**
	* issue tracker plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { the plugin config
$plugin=array(
	'name' => 'Issue Tracker',
	'admin' => array(
		'page_type' => 'IssueTracker_admin',
		'menu' => array(
			'Site Options>Issue Tracker'=>'plugin.php?_plugin=issue-tracker&amp;_page=issue-tracker'
			),
	),
	'description' => 'project management, issue tracking, task management',
	'frontend' => array(
		'page_type' => 'IssueTracker_front'
	),
	'version'=>9
);
// }

// { IssueTracker_admin

/**
	* show the admin page for the issue tracker
	*
	* @param object $page      the page object
	* @param array  $page_vars the page's variables
	*
	* @return string HTML of the admin form
	*/
function IssueTracker_admin($page, $page_vars) {
	require SCRIPTBASE.'ww.plugins/issue-tracker/admin/page_type.php';
	return $html;
}

// }
// { IssueTracker_front

/**
	* show registration or login page
	*
	* @param object $PAGEDATA the page object
	*
	* @return HTML of the page
	*/
function IssueTracker_front($PAGEDATA) {
	require SCRIPTBASE.'ww.plugins/issue-tracker/frontend/page_type.php';
	global $unused_uri;
	if (isset($unused_uri) && $unused_uri) {
		redirect(
			$PAGEDATA->getRelativeURL().'#'.preg_replace('/\/$/', '', $unused_uri)
		);
	}
	if (isset($_SESSION['userdata'])) {
		WW_addCSS('/j/jquery.multiselect/jquery.multiselect.css');
		WW_addScript('/j/jquery.multiselect/jquery.multiselect.min.js');
	}
	return $PAGEDATA->render().$html.__FromJson(@$PAGEDATA->vars['footer']);
}

// }
