<?php
/**
	* Online-Store common functions
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

/**
	* function for calculating current Online-Store basket total
	*
	* @return float
	*/
function OnlineStore_calculateTotal() {
	$total=0;
	foreach ($_SESSION['online-store']['items'] as $item) {
		$total+=($item['cost']*$item['amt']);
	}
	$_SESSION['online-store']['total']=$total;
	return $total;
}
