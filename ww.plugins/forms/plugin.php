<?php
/**
  * Form plugin definition file
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

// { plugin definition
$plugin=array(
	'name' => function() {
		return __('Form');
	},
	'admin' => array(
		'page_types' => array('forms'),
		'page_type' => 'Form_adminPageForm'
	),
	'description' => function() {
		return __('Allows forms to be created so visitors can contact you');
	},
	'frontend' => array(
		'page_type' => 'Form_frontend'
	),
	'version' => 8
);
// }

// { Form_adminPageForm

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
    /** @noinspection PhpUnusedLocalVariableInspection */
    $id=$page['id'];
	$c='';
	if ($edit) {
		require SCRIPTBASE.'ww.plugins/forms/admin/save.php';
	}
	require SCRIPTBASE.'ww.plugins/forms/admin/form.php';
	return $c;
}

// }
// { Form_frontend

/**
  * display the page's form
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the form's HTML
  */
function Form_frontend($PAGEDATA) {
	require SCRIPTBASE.'ww.plugins/forms/frontend/show.php';
	WW_addScript('/j/jquery.tooltip.min.js');
	return $PAGEDATA->render()
		.Form_show($PAGEDATA->dbVals, $PAGEDATA->vars)
		.@$PAGEDATA->vars['footer'];
}

// }
