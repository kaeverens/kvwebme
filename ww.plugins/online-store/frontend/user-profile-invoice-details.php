<?php
/**
	* list details of a specific order by a user
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

switch ($iid['status']) {
	case '0':
		$status='Unpaid';
	break;
	case '1':
		$status='Paid';
	break;
	case '2':
		$status='Delivered';
	break;
	case '3':
		$status='Cancelled';
	break;
	case '4':
		$status='Authorised';
	break;
	default:
		$status='Status number '.$iid['status'].' is unknown';
	break;
}
$html='<h2>'.__('Online Store - Order History - Invoice', 'core')
	.' '.$iid['id'].'</h2>'
	.'<a href="'.$PAGEDATA->getRelativeUrl().'">'.__('back to profile', 'core')
	.'</a>'
	.'<table class="onlinestore-purchase-details">'
	.'<tr><th>'.__('Invoice ID').'</th><td>'.$iid['id'].'</td></tr>'
	.'<tr><th>'.__('Date').'</th><td>'
	.Core_DateM2H($iid['date_created'], 'datetime').'</td></tr>'
	.'<tr><th>'.__('Total').'</th><td>'.OnlineStore_numToPrice($iid['total']).'</td></tr>'
	.'<tr><th>'.__('Status').'</th><td>'.$status.'</td></tr>'
	.'<tr><th>'.__('Invoice').'</th><td><a href="/a/p=online-store/f=invoicePdf/id='
	.$iid['id'].'">'.__('PDF').'</a></td></tr>';
// { list of items
$html.='<tr><th>'.__('Items Purchased').'</th><td>'
	.'<table class="onlinestore-item-details"><tr><th>'.__('Description').'</th>'
	.'<th>'.__('Amount').'</th><th>'.__('Cost').'</th></tr>';
$items=json_decode($iid['items'], true);
foreach ($items as $item) {
	$desc=$item['short_desc'];
	if ($item['long_desc']) {
		$desc.='<br/>'.$item['long_desc'];
	}
	$html.='<tr><td><a href="'.$item['url'].'">'.$desc.'</a></td>'
		.'<td>'.$item['amt'].'</td><td>'.OnlineStore_numToPrice($item['cost'])
		.'</td></tr>';
}
$html.='</table></td></tr>';
// }
$html.='</table>';
