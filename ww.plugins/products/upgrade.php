<?php
/**
  * upgrade script for the Products plugin
  *
  * PHP Version 5
  *
  * @category ProductsPlugin
  * @package  WebWorksWebme
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     www.kvweb.me
 */
if ($version==0) { // product types
	dbQuery(
		'CREATE TABLE products_types (
	  id int(11) NOT NULL auto_increment,
	  name text NOT NULL,
		short_template text NOT NULL,
		long_template text NOT NULL,
		show_product_variants smallint(6) default 1,
		show_related_products smallint(6) default 1,
		show_contained_products smallint(6) default 1,
		show_countries smallint(6) default 0,
		PRIMARY KEY  (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // data fields
	dbQuery('alter table products_types add data_fields text');
	$version=2;
}
if ($version==2) { // multi- and single-view templates
	dbQuery('alter table products_types change short_template multiview_template text');
	dbQuery('alter table products_types change long_template singleview_template text');
	$version=3;
}
if ($version==3) { // products table
	dbQuery(
		'CREATE TABLE products (
		id int(11) NOT NULL auto_increment,
		name text,
		product_type_id int(11) default 0,
		default_image text,
		enabled smallint(6) default 1,
		date_created datetime default NULL,
		data_fields text,
		PRIMARY KEY  (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=4;
}
if ($version==4) { // products_categories
	dbQuery(
		'CREATE TABLE products_categories (
		id int(11) NOT NULL auto_increment,
		name text,
		parent_id int(11) default 0,
		enabled smallint(1) default 0,
		PRIMARY KEY  (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	dbQuery('insert into products_categories values(1,"default",0,1)');
	$version=5;
}
if ($version==5) { // products_categories
	dbQuery(
		'CREATE TABLE products_categories_products (
		product_id int(11) default 0,
		category_id int(11) default 0
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=6;
}
if ($version==6) { // product images
	dbQuery('alter table products add images_directory text');
	$version=7;
}
if ($version==7) { // default image
	dbQuery('alter table products change default_image image_default int default 0');
	$version=8;
}
if ($version<10) { // products reviews table
	dbQuery(
		'CREATE TABLE products_reviews
		(
			id int NOT NULL auto_increment primary key,
			body text,
			user_id int default 0,
			product_id int default 0,
			rating smallint default 0,
			cdate date
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=10;
}
if ($version==10) { // create product relation tables
	dbQuery(
		'create table products_relation_types (
		id int auto_increment not null primary key,
		name text,
		one_way smallint default 0
		)engine=MyISAM default charset=utf8'
	);
	dbQuery(
		'create table products_relations (
		relation_id int default 0,
		from_id int default 0,
		to_id int default 0
		)engine=MyISAM default charset=utf8'
	);
	$version=11;
}
if ($version==11) { // cdate should be datetime
	dbQuery('alter table products_reviews change cdate cdate datetime');
	$version=12;
}
if ($version==12) { // Types needs an is_for_sale flag
	dbQuery('alter table products_types add is_for_sale smallint default 0');
	$version=13;
}
if ($version==13) { // Online store column for products
	dbQuery('alter table products add online_store_fields text');
	$version=14;
}
if ($version==14) { // prices_based_on_usergroup
	dbQuery(
		'alter table products_types add prices_based_on_usergroup tinyint default 0'
	);
	$version=15;
}
if ($version==15) { // add "associated_colour" to types and categories
	dbQuery(
		'alter table products_categories add associated_colour char(6) default '
		.'"ffffff"'
	);
	dbQuery(
		'alter table products_types add associated_colour char(6) default '
		.'"ffffff"'
	);
	$version=16;
}
if ($version==16) { // add header/footer to multi-view types
	dbQuery('alter table products_types add multiview_template_header text');
	dbQuery('alter table products_types add multiview_template_footer text');
	$version=17;
}
if ($version==17) { // add "meta" to product type table
	dbQuery('alter table products_types add meta text');
	$version=18;
}
if ($version==18) { // add "sortNum" to products_categories
	dbQuery('alter table products_categories add sortNum int default 0');
	$version=19;
}
if ($version==19) { // remove unused fields from product types
	dbQuery('alter table products_types drop show_product_variants');
	dbQuery('alter table products_types drop show_related_products');
	dbQuery('alter table products_types drop show_contained_products');
	dbQuery('alter table products_types drop show_countries');
	$version=20;
}
if ($version==20) { // add stock number
	dbQuery('alter table products add stock_number text');
	$version=21;
}
if ($version==21) { // product-type vouchers
	dbQuery('alter table products_types add is_voucher smallint default 0');
	dbQuery('alter table products_types add voucher_template text');
	$version=22;
}
if ($version==22) { // add voucher_value to products table
	dbQuery('alter table products add voucher_value float default 0');
	$version=23;
}
if ($version==23) { // sold_amt, stock_amt, stock_management
	dbQuery('alter table products add stock_amt int default 0');
	dbQuery('alter table products add sold_amt int default 0');
	dbQuery('alter table products_types add stock_management text');
	$version=24;
}
if ($version==24) { // activation/expiry dates
	dbQuery('alter table products add activates_on datetime default "0000-00-00"');
	dbQuery('alter table products add expires_on datetime default "0000-00-00"');
	$version=25;
}
if ($version==25) { // remove some silly ideas that are better done differently
	dbQuery('alter table products drop voucher_value');
	dbQuery('alter table products drop stock_amt');
	dbQuery('alter table products drop sold_amt');
	dbQuery('alter table products_types drop stock_management');
	dbQuery('alter table products_types add stock_control smallint default 0');
	$version=26;
}
if ($version==26) { // update expiry dates
	dbQuery('update products set expires_on="2100-01-01 00:00:00" where expires_on="0000-00-00 00:00:00"');
	$version=27;
}
if ($version==27) { // clear cron
	unset($DBVARS['cron-next']);
	$version=28;
}
if ($version==28) { // add owner
	dbQuery('alter table products add user_id int default 0');
	$version=29;
}
