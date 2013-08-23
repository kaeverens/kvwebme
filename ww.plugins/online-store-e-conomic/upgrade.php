<?php
/**
  * upgrade script for the onlinestore-economic plugin
  *
  * PHP Version 5
  *
  * @category Plugin
  * @package  Webme
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     www.kvweb.me
 */
if ($version==0) { // email template
	dbQuery(
		'insert into online_store_vars set'
		.' name="email_onlinestore-economic-email"'
		.', val="<p>Your invoice is attached.</p><p>If the attachment does not'
		.' load, please click the following link to access the invoice:</p>'
		.'{{INVOICE_URL}}"'
	);
	dbQuery(
		'insert into online_store_vars set'
		.' name="email_onlinestore-economic-email_template"'
		.', val="_body"'
	);
	dbQuery(
		'insert into online_store_vars set'
		.' name="email_onlinestore-economic-email_subject"'
		.', val="Invoice {{INVOICE_NUM}}"'
	);
	$version=1;
}
