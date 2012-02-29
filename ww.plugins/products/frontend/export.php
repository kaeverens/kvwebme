<?php
/**
	* export products to CSV
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'datatable-libs.php';
require_once dirname(__FILE__).'/../api.php';

$i=0;

header('Content-type: text/csv; Charset=utf-8');
header('Content-Disposition: attachment; filename="nfgws-export.csv"');

echo '"'.join('","', $columns)."\"\n";

for (; $i<$total_records; ++$i) {
	$arr=array();
	$p=Product::getInstance($products->product_ids[$i]);
	foreach ($columns as $name) {
		$arr[]=$p->getString($name);
	}
	echo Products_arrayToCSV($arr);
}
