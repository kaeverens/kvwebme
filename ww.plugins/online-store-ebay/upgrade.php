<?php
/**
  * upgrade script for the OnlineStoreEbay plugin
  *
  * PHP Version 5
  *
  * @category OnlineStoreEbayPlugin
  * @package  Webme
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     www.kvweb.me
 */
if ($version==0) {
	dbQuery('alter table products add ebay_currently_active int default 0;');
	dbQuery('alter table products add ebay_bids_start_at float default 0;');
	dbQuery('alter table products add ebay_buy_now_price float default 0;');
	dbQuery('alter table products add ebay_id bigint default 0;');
	dbQuery('alter table products_categories add ebay_id int default 0;');
	$version=1;
}
if ($version==1) {
	dbQuery('alter table products add ebay_how_many_to_sell int default 1;');
	$version=2;
}
