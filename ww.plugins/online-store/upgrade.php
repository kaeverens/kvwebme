<?php
/**
	* Upgrade file for the Online-Store plugin
	* 
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

if ($version==0) { // online_store_orders
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `online_store_orders` (
		`id` int(11) NOT NULL auto_increment,
		`form_vals` text,
		`invoice` text,
		`total` float,
		`date_created` datetime,
		PRIMARY KEY  (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // items field
	dbQuery('alter table online_store_orders add items text');
	$version=2;
}
if ($version==2) { // status
	dbQuery('alter table online_store_orders add status smallint default 0');
	$version=3;
}
if ($version==3) { // currency
	$DBVARS['online_store_currency']='EUR';
	$version=4;
}
if ($version==4) { // callback
	/* allow a callback to be set in the database to be called when
	 *   a payment has been completed
	 */
	dbQuery('alter table online_store_orders add callback text');
	$version=5;
}
if ($version==5) { // clear caches
	Core_cacheClear('pages');
	Core_cacheClear('products');
	$version=6;
}
if ($version==6) { // change _apply_vat to _vatfree
	$rs=dbAll('select id,online_store_fields from products');
	if ($rs !== false) {
		foreach ($rs as $r) {
			$f=str_replace(
				'"_apply_vat":"0"',
				'"_vatfree":"1"',
				$r['online_store_fields']
			);
			$f=str_replace(
				'"_apply_vat":"1"',
				'"_vatfree":"0"',
				$f
			);
			dbQuery(
				'update products set online_store_fields="'
				.addslashes($f).'" where id='.$r['id']
			);
		}
	}
	$version=7;
}
if ($version==7) { // add online_store_vouchers
	dbQuery(
		'create table online_store_vouchers(
		id int auto_increment not null primary key,
		name text,
		code text,
		user_constraints enum("public", "userlist"),
		users_list text,
		value float default 0,
		value_type enum("percentage", "value"),
		usages_per_user int,
		usages_in_total int,
		start_date date,
		end_date date
		) default charset=utf8'
	);
	$version=8;
}
if ($version==8) { // add user_id column to online_store_orders
	dbQuery(
		'alter table online_store_orders add user_id int default 0 after callback'
	);
	$version = 9;
}
if ($version==9) { // add online_store_lists table
	dbQuery(
		'create table online_store_lists('
		.'name text, user_id int, details text'
		.')default charset=utf8'
	);
	$version=10;
}
if ($version==10) { // add session table, for quickpay method
	dbQuery(
		'create table if not exists online_store_session('
		.'reference varchar(255) DEFAULT \'0\' NOT NULL,'
		.'data TEXT NOT NULL,'
		.'PRIMARY KEY (reference)'
		.')default charset=utf8'
	);
	$version=11;
}
if ($version==11) { // add meta, for tracking details from gateways
	dbQuery(
		'alter table online_store_orders add meta text'
	);
	$version=12;
}
if ($version==12) { // add authorised, for delayed credit card payments
	dbQuery(
		'alter table online_store_orders add authorised smallint default 0'
	);
	$version=13;
}
if ($version==13) { // change menus for new separated pages
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-admin.php';
	Core_adminMenusAdd(
		'Online Store>Orders', 'plugin.php?_plugin=online-store&amp;_page=orders'
	);
	$version=14;
}
if ($version==14) { // no longer using page_vars for invoices
	dbQuery(
		'create table online_store_vars('
		.'name text'
		.',val text'
		.')default charset=utf8'
	);
	$html=dbOne(
		'select value from page_vars where name="online_stores_invoice"', 'value'
	);
	if ($html) {
		$html=str_replace(array("\r", "\n"), ' ', $html);
		$html=str_replace(array('{', '}'), array('{{', '}}'), $html);
		$newhtml=$html;
		do {
			$html=$newhtml;
			$newhtml=preg_replace(
				'/({{literal}})(.*){{(.*)({{\/literal}})/', '\1\2{\3\4', $html
			);
			$newhtml=preg_replace(
				'/({{literal}})(.*)}}(.*)({{\/literal}})/', '\1\2}\3\4', $newhtml
			);
		} while($newhtml!=$html);
		$html=str_replace(array('{{literal}}', '{{/literal}}'), '', $html);
		dbQuery(
			'insert into online_store_vars set name="email_invoice"'
			.', val="'.addslashes($html).'"'
		);
	}
	$email=dbOne(
		'select value from page_vars where name="online_stores_admin_email"',
		'value'
	);
	if ($email) {
		dbQuery(
			'insert into online_store_vars set name="email_invoice_recipient"'
			.', val="'.addslashes($email).'"'
		);
	}
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-admin.php';
	Core_adminMenusAdd(
		'Online Store>Emails', 'plugin.php?_plugin=online-store&amp;_page=emails'
	);
	$version=15;
}
if ($version==15) { // no longer using page_vars for export variables
	// { export_dir
	$val=dbOne(
		'select value from page_vars where name="online_stores_exportdir"', 'value'
	);
	dbQuery(
		'insert into online_store_vars set name="export_dir"'
		.', val="'.addslashes($val).'"'
	);
	// }
	// { export_customers
	$val=dbOne(
		'select value from page_vars where name="online_stores_exportcustomers"',
		'value'
	);
	dbQuery(
		'insert into online_store_vars set name="export_customers"'
		.', val="'.addslashes($val).'"'
	);
	// }
	// { export_customers_filename
	$val=dbOne(
		'select value from page_vars'
		.' where name="online_stores_exportcustomers_filename"',
		'value'
	);
	dbQuery(
		'insert into online_store_vars set name="export_customers_filename"'
		.', val="'.addslashes($val).'"'
	);
	// }
	// { export_at_what_point
	$val=(int)dbOne(
		'select val from online_store_vars where name="invoices_by_email"',
		'val'
	);
	dbQuery(
		'insert into online_store_vars set name="export_at_what_point"'
		.', val="'.addslashes($val).'"'
	);
	// }
	$version=16;
}
