<?php
/**
	* definition file for Translate plugin
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
	'name' => 'Translate',
	'admin' => array(
		'page_type' => 'Translate_admin'
	),
	'description' => 'Use Google Translate to automatically translate pages.',
	'frontend' => array(
		'page_type' => 'Translate_frontend'
	),
	'triggers'=>array(
		'page-object-loaded'=>'Translate_checkCurrentPage'
	)
);
// }

/**
	* admin area Page form
	*
	* @param object $page Page array from database
	* @param array  $vars Page's custom variables
	*
	* @return string
	*/
function Translate_admin($page,$vars) {
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}


/**
	* stub function to load frontend page-type
	*
	* @param object $PAGEDATA the current page
	*
	* @return string
	*/
function Translate_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/check.php';
	return $PAGEDATA->render();
}

/**
	* switches the reader to a translation if one exists
	*
	*	@param object $PAGEDATA page object
	*
	* @return void
	*/
function Translate_checkCurrentPage($PAGEDATA) {
	// { if this is a translation page, and no language is selected, select this one.
	if ($PAGEDATA->type=='translate' && !isset($_SESSION['translate-lang'])) {
		$_SESSION['translate-lang']=$PAGEDATA->vars['translate_language'];
	}
	// }
	// { if no language is selected, then return
	if (!isset($_SESSION['translate-lang']) || !$_SESSION['translate-lang']) {
		return;
	}
	// }
	// { various checks if this page is a translation one
	$page_to_translate=$PAGEDATA->id;
	if ($PAGEDATA->type=='translate') {
		// { if this page's language is the selected one, return
		if ($PAGEDATA->vars['translate_language']==$_SESSION['translate-lang']) {
			return;
		}
		// }
		$page_to_translate=(int)$PAGEDATA->vars['translate_page_id'];
	}
	$trs=dbAll(
		'select page_id from page_vars where name="translate_page_id" and value='
		.$page_to_translate
	);
	// { try to find a version of the current page in the selected language
	if ($trs===false || !count($trs)) {
		return;
	}
	$ids=array();
	foreach ($trs as $tr) {
		$ids[]=$tr['page_id'];
	}
	$page_id=dbOne(
		'select page_id from page_vars where name="translate_language" and value="'
		.addslashes($_SESSION['translate-lang']).'" limit 1',
		'page_id'
	);
	// { if none found, return
	if ($page_id===false) {
		return;
	}
	// }
	$page=Page::getInstance($page_id);
	if ($page->id) {
		redirect($page->getRelativeUrl());
	}
	// }
}

if (isset($_REQUEST['translate_lang'])) {
	$_SESSION['translate-lang']=$_REQUEST['translate_lang'];
}
