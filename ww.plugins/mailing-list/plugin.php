<?php
/*
	Webme Mailing List Plugin v0.2
	File: plugin.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/
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
	'description' => 'Add a mailing list widget that readers can subscribe to.',
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
function MailingList_showForm(){
	include_once SCRIPTBASE . 'ww.plugins/mailing-list/frontend/mailing-list.php';
	return show_form();
}
