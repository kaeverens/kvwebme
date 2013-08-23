<?php
/*
	Webme Mailing List Plugin
	File: admin/actions.save.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Developer: Kae Verens <kae@verens.com>
	Report Bugs: <kae@verens.com>
*/

if (isset($_POST['vermail_save'])) {
	$from=addslashes($_POST['from']);
	$subject=addslashes($_POST['subject']);
	$body=addslashes($_POST['body']);
	if ($from==''||$subject==''||$body=='') {
		$updated='Please do not leave fields blank.';
	}
	else {
		dbQuery(
			'update mailing_list_options set value="'.$from.'" where name="from"'
		);
		dbQuery(
			'update mailing_list_options set value="'.$subject
			.'" where name="subject"'
		);
		dbQuery(
			'update mailing_list_options set value="'.$body.'" where name="body"'
		);
		$updated='An item\'s details have been updated.';
	}
}
if (isset($_POST['vermail_restore'])) {
	$from='noreply@webme.eu';
	$subject='Mailing List SUbscription';
	$body='Hi, \n
			You or someone using your email address has applied to join our maili'
			.'ng list. \n
			To approve this subscription please click on the link below: \n
			%link% \n
			Thanks, \n
			The Team';
	dbQuery(
		'update mailing_list_options set value="'.addslashes($from)
		.'" where name="from"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($subject)
		.'" where name="subject"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($body)
		.'" where name="body"'
	);
	$updated='The database has been restored.';	
}
if (isset($_POST['front_sub'])) {
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['dis_sub'])
		.'" where name="dis_sub"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['col_name'])
		.'" where name="col_name"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['col_mobile'])
		.'" where name="col_mobile"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['inp_em'])
		.'" where name="inp_em"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['inp_nm'])
		.'" where name="inp_nm"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['inp_mb'])
		.'" where name="inp_mb"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['inp_sub'])
		.'" where name="inp_sub"'
	);
	$updated='An item\'s details have been updated.';
}
if (isset($_POST['admin_sub'])) {
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['dis_pend'])
		.'" where name="dis_pend"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['use_js'])
		.'" where name="use_js"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['use_bcc'])
		.'" where name="use_bcc"'
	);
	dbQuery(
		'update mailing_list_options set value="'.addslashes($_POST['email'])
		.'" where name="email"'
	);
	$updated='An item\'s details have been updated.';
}
