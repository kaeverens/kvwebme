<?php
/**
	* emails page for Online Store
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { handle actions
if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$type=$_REQUEST['onlinestore-emails-type'];
	if (preg_replace('/[^a-z_]/', '', $type)!=$type) {
		echo __('no hacking, please');
		Core_quit();
	}
	dbQuery('delete from online_store_vars where name like "email_'.$type.'%"');
	dbQuery(
		'insert into online_store_vars set name="email_'.$type.'"'
		.', val="'.addslashes($_REQUEST['onlinestore-emails-body']).'"'
	);
	dbQuery(
		'insert into online_store_vars set name="email_'.$type.'_template"'
		.', val="'.addslashes($_REQUEST['onlinestore-emails-template']).'"'
	);
	dbQuery(
		'insert into online_store_vars set name="email_'.$type.'_recipient"'
		.', val="'.addslashes($_REQUEST['onlinestore-emails-recipient']).'"'
	);
	dbQuery(
		'insert into online_store_vars set name="email_'.$type.'_replyto"'
		.', val="'.addslashes($_REQUEST['onlinestore-emails-replyto']).'"'
	);
	dbQuery(
		'insert into online_store_vars set name="email_'.$type.'_subject"'
		.', val="'.addslashes($_REQUEST['onlinestore-emails-subject']).'"'
	);
}
// }
// { the form
// { setup variables
$email=isset($_REQUEST['onlinestore-emails-type'])
	?$_REQUEST['onlinestore-emails-type']
	:'invoice';
$rs=dbAll(
	'select * from online_store_vars where name like "email_'.$email.'%"',
	'name'
);
// }
echo '<form id="onlinestore-emails" method="post" action="'
	.'/ww.admin/plugin.php?_plugin=online-store&amp;_page=emails">'
	.'<table>';
// { show list of email types
echo '<tr><th>Email type</th><td><select name="onlinestore-emails-type">';
// TODO: Translate
$emails=array(
	'invoice'=>'the invoice/receipt to send to the customer',
	'order_made_admin'=>'email to send to admin when an order is made',
	'order_made_customer'=>'email to send to customer when an order is made',
	'order_dispatched'=>'email to send when the order is dispatched'
);
foreach ($emails as $k=>$v) {
	echo '<option value="'.$k.'"';
	if ($k==$email) {
		echo ' selected="selected"';
	}
	echo '>'.$v.'</option>';
}
echo '</select></td></tr>';
// }
// { email template
echo '<tr><th>'.__('Template').'</th>'
	.'<td><select name="onlinestore-emails-template">';
$rs2=dbAll('select name from email_templates order by name');
if (!isset($rs['email_'.$email.'_template'])) {
	$rs['email_'.$email.'_template']='_body';
}
foreach ($rs2 as $r) {
	if ($r['name']=='_footer' || $r['name']=='_header') {
		continue;
	}
	echo '<option';
	if ($r['name']==@$rs['email_'.$email.'_template']['val']) {
		echo ' selected="selected"';
	}
	echo '>'.$r['name'].'</option>';
}
echo '</select></td></tr>';
// }
// { subject
echo '<tr><th>'.__('Subject of the email').'</th>'
	.'<td><input name="onlinestore-emails-subject" class="wide"'
	.' value="'.htmlspecialchars(@$rs['email_'.$email.'_subject']['val']).'"/></td>'
	.'</tr>';
// }
// { recipient
echo '<tr><th>'.__('Admin who receives a copy of this email').'</th>'
	.'<td><input type="email" name="onlinestore-emails-recipient"'
	.' value="'.htmlspecialchars(@$rs['email_'.$email.'_recipient']['val']).'"/></td>'
	.'</tr>';
// }
// { replyto
echo '<tr><th>'.__('Reply-to address').'</th>'
	.'<td><input type="email" name="onlinestore-emails-replyto"'
	.' value="'.htmlspecialchars(
		@$rs['email_'.$email.'_replyto']['val']
	).'"/></td>'
	.'</tr>';
// }
// { body
echo '<tr><th>'.__('Email Body').'</th>';
$body=@$rs['email_'.$email]['val']
	?$rs['email_'.$email]['val']
	:file_get_contents(dirname(__FILE__).'/email_template_'.$email.'.html');
echo '<td>'.ckeditor('onlinestore-emails-body', $body).'</td></tr>';
// }
echo '<tr><th></th><td>'
	.'<input type="hidden" name="action" value="save"/>'
	.'<button>'.__('Save').'</button></td></tr></table></form>';
// }
WW_addScript('online-store/admin/emails.js');
