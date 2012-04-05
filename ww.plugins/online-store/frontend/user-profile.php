<?php
/**
	* lists past orders made by the user
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Conor MacAoidh <conor@kvsites.ie>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

$html = '<h2 class="__" lang-context="core">Online Store - Order History</h2>';

$history = array();
$orders = dbAll(
	'select id,status,total,user_id,date_created from online_store_orders'
	.' order by date_created desc'
);
foreach ($orders as $order) {
	if ($order[ 'user_id' ] != 0 && $order[ 'user_id' ] == $user[ 'id' ]) {
		array_push($history, $order);
	}
}

if (count($history) == 0) {
	return $html .= '<p><i>No recent orders</i></p>';
}

WW_addScript('online-store/frontend/user-profile.js');

$html .= '<table id="online_store_orders" style="border:1px solid #ccc;'
	.'margin:10px">
	<tr>
		<th class="__" lang-context="core">Date</th>
		<th class="__" lang-context="core">Amount</th>
		<th class="__" lang-context="core">Status</th>
		<th class="__" lang-context="core">Invoice</th>
	</tr>';

foreach ($history as $order) {
	$status = ( $order[ 'status' ] == 1 ) ? 'Paid' : 'Unpaid';
	$html .= '<tr>
		<td>' . Core_dateM2H($order[ 'date_created' ]) . '</td>
		<td>' . $order[ 'total' ] . '</td>
		<td>' . $status . '</td>
		<td>
			<a href="javascript:os_invoice(' . $order[ 'id' ] . ')">Invoice</a> 
		  <a href="javascript:os_invoice(' . $order[ 'id' ] . ', true)">(print)</a>
		</td>
	</tr>';
}  

$html .= '</table>';
