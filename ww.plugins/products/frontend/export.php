<?php
require 'datatable-libs.php';

$i=0;

header('Content-type: text/csv; Charset=utf-8');
header('Content-Disposition: attachment; filename="nfgws-export.csv"');

function sputcsv($row, $delimiter = ',', $enclosure = '"', $eol = "\n") {
    static $fp = false;
    if ($fp === false) {
        $fp = fopen('php://temp', 'r+'); // see http://php.net/manual/en/wrappers.php.php - yes there are 2 '.php's on the end.
        // NB: anything you read/write to/from 'php://temp' is specific to this filehandle
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
