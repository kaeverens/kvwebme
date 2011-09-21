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

require 'datatable-libs.php';

$i=0;

header('Content-type: text/csv; Charset=utf-8');
header('Content-Disposition: attachment; filename="nfgws-export.csv"');

/**
	* convert an array to a CSV row
	*
	* @param array  $row       row data
	* @param string $delimiter what to separate the fields by
	* @param string $enclosure how should strings be enclosed
	* @param string $eol       what end-of-line character to use
	*
	* @return string the CSV row
	*/
function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n") {
	static $fp = false;
	if ($fp === false) {
		$fp = fopen('php://temp', 'r+');
	}
	else {
		rewind($fp);
	}
	if (fputcsv($fp, $row, $delimiter, $enclosure) === false) {
		return false;
	}
	rewind($fp);
	$csv = fgets($fp);
	if ($eol != PHP_EOL) {
		$csv = substr($csv, 0, (0 - strlen(PHP_EOL))) . $eol;
	}
	return $csv;
}

echo '"'.join('","', $columns)."\"\n";

for (; $i<$total_records; ++$i) {
	$arr=array();
	$p=Product::getInstance($products->product_ids[$i]);
	foreach ($columns as $name) {
		$arr[]=$p->getString($name);
	}
	echo sputcsv($arr);
}
