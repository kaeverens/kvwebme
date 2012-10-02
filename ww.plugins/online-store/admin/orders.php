<?php
/**
	* orders page for Online Store
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $online_store_currencies, $DBVARS;
if (isset($_REQUEST['online_store_currency'])
	&& isset($online_store_currencies[$_REQUEST['online_store_currency']])
) {
	$DBVARS['online_store_currency']=$_REQUEST['online_store_currency'];
	Core_configRewrite();
}
$csym=$online_store_currencies[$DBVARS['online_store_currency']][0];
// { are there any authorised payments?
$authrs=dbAll(
	'select id,date_created,total,status from online_store_orders where authorised'
);
$has_authrs=count($authrs)?1:0;
// }

$c='<div class="tabs">';
$c.= '<ul>'
	.'<li><a href="#online-store-orders">'.__('Orders').'</a></li>';
if ($has_authrs) { // show authorised payments (for retrieval)
	$c.='<li><a href="#online-store-authorised">'.__('Authorised Payments').'</a></li>';
}
$c.='</ul>';
// { orders
$c.='<div id="online-store-orders">';
if (!isset($_SESSION['online-store'])) {
	$_SESSION['online-store']=array();
}
if (!isset($_SESSION['online-store']['status'])) {
	$_SESSION['online-store']['status']=1;
}
if (isset($_REQUEST['online-store-status'])) {
	$_SESSION['online-store']['status']=(int)$_REQUEST['online-store-status'];
}
$c.='<p>'
	.__('This list shows orders with the status: ')
	.'<select id="online-store-status">';
$statii=array(
	'0'=>__('Unpaid'),
	'4'=>__('Authorised'),
	'1'=>__('Paid'),
	'2'=>__('Delivered'),
	'3'=>__('Cancelled')
);
foreach ($statii as $k=>$v) {
	$c.='<option value="'.$k.'"';
	if ($k===$_SESSION['online-store']['status']) {
		$c.=' selected="selected"';
	}
	$c.='>'.htmlspecialchars($v).'</option>';
}
$c.='</select></p>';
// { filter for SQL
if ($_SESSION['online-store']['status']==1) {
	$filter='status=1 or authorised=1';
}
else {
	$filter='status='.(int)$_SESSION['online-store']['status'];
}
// }
$rs=dbAll(
	'select status,id,total,date_created,authorised from online_store_orders where '
	.$filter.' order by date_created desc'
);
if (is_array($rs) && count($rs)) {
	$c.='<div style="margin:0 10%">'
		.'<table id="onlinestore-orders-table" width="100%" class="desc"><thead><tr>'
		.'<th><input type="checkbox" id="onlinestore-orders-selectall"/></th>'
		.'<th>'.__('ID').'</th>'
		.'<th>'.__('Date').'</th>'
		.'<th>'.__('Amount').'</th>'
		.'<th>'.__('Items').'</th>'
		.'<th>'.__('Invoice').'</th>'
		.'<th>'.__('Checkout Form').'</th>'
		.'<th>'.__('Status').'</th>'
		.'</tr></thead><tbody>';
	foreach ($rs as $r) {
		$c.='<tr data-id="'.$r['id'].'">'
			.'<td><input class="mass-actions" type="checkbox"/></td>'
			.'<td>'.$r['id'].'</td>'
			.'<td><span style="display:none">'.$r['date_created'].'</span>'
			.Core_dateM2H($r['date_created']).'</td><td>'
			.$csym.sprintf('%.2f', $r['total'])
			.'</td>'
			.'<td><a href="javascript:os_listItems('.$r['id'].')">'.__('Items').'</a></td>'
			.'<td><a href="javascript:os_invoice('.$r['id'].')">'.__('Invoice').'</a>'
			.' (<a href="javascript:os_invoice('.$r['id'].',true)">'.__('Print').'</a>)</td>'
			.'<td>'
			.'<a href="javascript:onlinestoreFormValues('.$r['id'].')">'.__('Checkout Form').'</a>'
			.'</td>'
			.'<td><a href="javascript:onlinestoreStatus('.$r['id'].','
			.(int)$r['status'].')" '
			.'id="os_status_'.$r['id'].'">'
			.htmlspecialchars($statii[(int)$r['status']]).'</a>';
		if ($r['authorised']) {
			$c.=' <strong>'.__('Authorised').'</strong>';
		}
		$c.='</td></tr>';
	}
	$c.='</tbody></table></div>'
		.'<select id="onlinestore-orders-action"><option value="0"> -- </option>'
		.'<option value="1">'.__('Mark as Unpaid').'</option>'
		.'<option value="2">'.__('Mark as Paid').'</option>'
		.'<option value="3">'.__('Mark as Delivered').'</option>'
		.'<option value="4">'.__('Download as PDF').'</option>'
		.'</select>';
}
else {
	$c.='<em>'.__('No orders with this status exist').'</em>';
}
$c.='</div>';
// }
// { authorised payments
if ($has_authrs) {
	$c.='<div id="online-store-authorised"><table class="wide"><tr><th>'
		.'<input type="checkbox"/></th><th>'.__('ID').'</th><th>'.__('Date').'</th><th>'.__('Total').'</th>'
		.'<th>'.__('Status').'</th></tr>';
	foreach ($authrs as $r) {
		$c.='<tr id="capture'.$r['id'].'"><td><input type="checkbox" id="auth'
			.$r['id'].'"/></td>'
			.'<td>'.$r['id'].'</td><td>'.Core_dateM2H($r['date_created']).'</td>'
			.'<td>'.$r['total'].'</td><td>'.$statii[(int)$r['status']].'</td></tr>';
	}
/* TODO: translation needed */
	$c.='</table><input type="button" value="Capture selected transactions"/>';
	$c.='</div>';
}
// }
$c.='</div>';

echo $c;
WW_addScript('/ww.plugins/online-store/admin/orders.js');
