<?php
/**
	* kvWebME A/B Testing plugin admin page rebuild script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

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
