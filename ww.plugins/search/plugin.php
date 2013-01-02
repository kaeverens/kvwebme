<?php
/**
	* search
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
	'name' => 'Search',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/search/admin/widget-form.php'
		)
	),
	'description' => 'Add a search box to a panel.',
	'frontend' => array(
		'widget' => 'Search_displayInputBox'
	),
	'version'=>0
);

// { Search_displayInputBox

/**
	* Search_displayInputBox
	*
	* @param array $vars variables
	*
	* @return html
	*/
function Search_displayInputBox($vars) {
	require SCRIPTBASE.'ww.plugins/search/frontend/widget.php';
	return $html;
}

// }
