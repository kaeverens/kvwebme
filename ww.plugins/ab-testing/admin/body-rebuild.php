<?php
$parts=array();
foreach ($_REQUEST['abtesting'] as $p) {
	if ($p!='') {
		$parts[]=$p;
	}
}
if (count($parts)>1) {
	$body=join('<div>ABTESTINGDELIMITER</div>', $parts);
}
else {
	$body=$parts[0];
}
