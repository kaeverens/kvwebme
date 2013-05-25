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
if ($version==16) { // invoice ID
	dbQuery('alter table online_store_orders add invoice_num int default 0');
	$version=17;
}
if ($version==17) {
	dbQuery('update online_store_orders set invoice_num=id');
	$version=18;
}
if ($version==18) { // move OS fields directly into products database table
	dbQuery('alter table products add os_base_price float default 0;');
	dbQuery('alter table products add os_trade_price float default 0;');
	dbQuery('alter table products add os_sale_price float default 0;');
	dbQuery('alter table products add os_sale_price_type smallint default 0;');
	dbQuery('alter table products add os_bulk_price float default 0;');
	dbQuery('alter table products add os_bulk_amount smallint default 0;');
	dbQuery('alter table products add os_weight float default 0;');
	dbQuery('alter table products add os_tax_free smallint default 0;');
	dbQuery('alter table products add os_custom_tax float default 0;');
	dbQuery('alter table products add os_free_delivery smallint default 0;');
	dbQuery('alter table products add os_not_discountable smallint default 0;');
	dbQuery('alter table products add os_amount_sold int default 0;');
	dbQuery('alter table products add os_amount_in_stock int default 0;');
	dbQuery(
		'alter table products add os_amount_allowed_per_purchase smallint default 0'
	);
	$version=19;
}
if ($version<22) {
	$ps=dbAll('select id,online_store_fields from products');
	foreach ($ps as $p) {
		if (!$p['online_store_fields']) {
			continue;
		}
		$os=json_decode($p['online_store_fields'], true);
		$sql=
			'update products set '
			.' os_base_price='.(isset($os['_price'])?(float)$os['_price']:0)
			.', os_trade_price='.(isset($os['_trade_price'])?(float)$os['_trade_price']:0)
			.', os_sale_price='.(isset($os['_sale_price'])?(float)$os['_sale_price']:0)
			.', os_sale_price_type='.(isset($os['_sale_price_type'])?(float)$os['_sale_price_type']:0)
			.', os_bulk_price='.(isset($os['_bulk_price'])?(float)$os['_bulk_price']:0)
			.', os_bulk_amount='.(isset($os['_bulk_amount'])?(float)$os['_bulk_amount']:0)
			.', os_weight='.(isset($os['_weight(kg)'])?(float)$os['_weight(kg)']:0)
			.', os_tax_free='.(isset($os['_vatfree'])?(float)$os['_vatfree']:0)
			.', os_custom_tax='.(isset($os['_custom_vat_amount'])?(float)$os['_custom_vat_amount']:0)
			.', os_free_delivery='.(isset($os['_deliver_free'])?(float)$os['_deliver_free']:0)
			.', os_not_discountable='.(isset($os['_not_discountable'])?(float)$os['_not_discountable']:0)
			.', os_amount_sold='.(isset($os['_sold_amt'])?(float)$os['_sold_amt']:0)
			.', os_amount_in_stock='.(isset($os['_stock_amt'])?(float)$os['_stock_amt']:0)
			.', os_amount_allowed_per_purchase='.(isset($os['_max_allowed'])?(float)$os['_max_allowed']:0)
			.' where id='.$p['id']
		;
		dbQuery($sql);
	}
	$version=22;
}
if ($version==22) { // add voucher_value
	dbQuery('alter table products add os_voucher_value float default 0;');
	$ps=dbAll(
		'select id,online_store_fields from products'
		.' where online_store_fields like "%_voucher_value%"'
	);
	foreach ($ps as $p) {
		if (!$p['online_store_fields']) {
			continue;
		}
		$os=json_decode($p['online_store_fields'], true);
		$sql=
			'update products set '
			.', os_voucher_value='.(isset($os['_voucher_value'])?(float)$os['_voucher_value']:0)
			.' where id='.$p['id']
		;
		dbQuery($sql);
	}
	$version=23;
}
if ($version==23) { // add supplier price
	dbQuery('alter table products add os_supplier_price float default 0;');
	dbQuery('update products set os_supplier_price=os_base_price');
	$version=24;
}
