<?php
/**
	* Upgrade file for the Online-Store plugin
	* 
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
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
	cache_clear('pages');
	cache_clear('products');
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

$DBVARS[$pname.'|version']=$version;
config_rewrite();
