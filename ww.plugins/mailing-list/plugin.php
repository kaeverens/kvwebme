<?php
/**
	* definition file for the WebME mailing list plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { plugin config
$plugin=array(
	'name' => 'Mailing List',
	'admin' => array(
		'menu' => array(
			'Communication>Mailing List'=>'index'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/mailing-list/admin/widget-form.php'
		)
	),
	'description' => 'DEPRECATED. use the "Mailing Lists" plugin instead.',
	'frontend' => array(
		'template_functions' => array(
			'MAILING_LIST' => array(
				'function' => 'MailingList_showForm'
			)
		),
		'widget' => 'MailingList_showForm'
	),
	'version' => '4'
);
// }

function MailingList_showForm(){
	require_once SCRIPTBASE.'ww.plugins/mailing-list/frontend/mailing-list.php';
	return Mailinglist_showForm2();
}
