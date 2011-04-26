<?php
/**
	* admin area page-type form for Online-Store
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $online_store_currencies,$DBVARS;
if (isset($_REQUEST['online_store_currency'])
	&& isset($online_store_currencies[$_REQUEST['online_store_currency']])
) {
	$DBVARS['online_store_currency']=$_REQUEST['online_store_currency'];
	config_rewrite();
}
$csym=$online_store_currencies[$DBVARS['online_store_currency']][0];
$c='<div class="tabs">';
$c.= '<ul>';
$c.='<li><a href="#online-store-orders">Orders</a></li>';
$c.='<li><a href="#online-store-delivery">Postage and Packaging</a></li>';
$c.='<li><a href="#online-store-form">Form</a></li>';
$c.='<li><a href="#online-stores-fields">Fields</a></li>';
$c.='<li><a href="#online-store-invoice">Invoice</a></li>';
$c.='<li><a href="#online-store-payment">Payment Details</a></li>';
$c.='<li><a href="#online-store-countries">Countries</a></li>';
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
	.'This list shows orders with the status: '
	.'<select id="online-store-status">';
$arr=array('Unpaid','Paid','Paid and Delivered');
foreach ($arr as $k=>$v) {
	$c.='<option value="'.$k.'"';
	if ($k==$_SESSION['online-store']['status']) {
		$c.=' selected="selected"';
	}
	$c.='">'.htmlspecialchars($v).'</option>';
}
$c.='</select>.</p>';
$rs=dbAll(
	'select status,id,total,date_created from online_store_orders where status='
	.((int)$_SESSION['online-store']['status']).' order by date_created desc'
);
if (is_array($rs) && count($rs)) {
	$c.='<div style="margin:0 20%">'
		.'<table width="100%" class="datatable desc"><thead><tr>'
		.'<th>Date</th>'
		.'<th>Amount</th>'
		.'<th>Invoice</th>'
		.'<th>Checkout Form</th>'
		.'<th>Status</th>'
		.'</tr></thead><tbody>';
	foreach ($rs as $r) {
		$c.='<tr><td><span style="display:none">'.$r['date_created'].'</span>'
			.date_m2h($r['date_created']).'</td><td>'.$csym.sprintf('%.2f',$r['total'])
			.'</td>'
			.'<td><a href="javascript:os_invoice('.$r['id'].')">Invoice</a>'
			.' (<a href="javascript:os_invoice('.$r['id'].',true)">print</a>)</td>'
			.'<td>'
			.'<a href="javascript:os_form_vals('.$r['id'].')">Checkout Form</a>'
			.'</td>'
			.'<td><a href="javascript:os_status('.$r['id'].','
			.(int)$r['status'].')" '
			.'id="os_status_'.$r['id'].'">'
			.htmlspecialchars($arr[(int)$r['status']]).'</a>'
			.'</td></tr>';
	}
	$c.='</tbody></table></div>';
}
else {
	$c.='<em>No orders with this status exist.</em>';
}
$c.='</div>';
// }
// { postage and packaging
if (!isset($vars['online_stores_postage'])) {
	$vars['online_stores_postage']='[]';
}
$c.='<div id="online-store-delivery">'
	.'<div id="postage_wrapper"></div>'
	.'<input type="hidden" name="page_vars[online_stores_postage]" id="postage" value="'
	.htmlspecialchars($vars['online_stores_postage']).'" />';
$c.='</div>';
// }
// { form
$c.='<div id="online-store-form">';
$c.='<p>This is the form that will be presented as the checkout.</p>';
if ($page['body']==''
	|| $page['body']=='<h1>'.htmlspecialchars($page['name']).'</h1><p>&nbsp;</p>'
) {
	$page['body']
		=file_get_contents(dirname(__FILE__).'/body_template_sample.html');
}
$c.=ckeditor('body', $page['body']);
$c.='</div>';
// }
// { form fields
if (!isset($vars['online_stores_fields'])
	|| !$vars['online_stores_fields']
) {
	$vars['online_stores_fields']='{}';
}
$c.='<div id="online-stores-fields">'
	.'<script>var os_fields='.$vars['online_stores_fields'].';</script>'
	.'<input type="hidden" name="page_vars[online_stores_fields]" value="'
	.htmlspecialchars($vars['online_stores_fields']).'" />'
	.'</div>';
// }
// { invoice details
$c.='<div id="online-store-invoice">';
$c.='<p>This is what will be sent out to the buyer after the payment succeeds.'
	.'</p>';
if (!isset($vars['online_stores_invoice']) || $vars['online_stores_invoice']=='') {
	$vars['online_stores_invoice']=file_get_contents(
		dirname(__FILE__).'/invoice_template_sample.html'
	);
}
$c.=ckeditor('page_vars[online_stores_invoice]', $vars['online_stores_invoice']);
$c.='</div>';
// }
// { payment details
$c.='<div id="online-store-payment">';
$c.='<table style="width:100%">';
// { admin email address
$c.='<tr><th style="width:20%">Admin email address</th><td>'
	.'<input type="email" name="page_vars[online_stores_admin_email]"';
if (isset($vars['online_stores_admin_email'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_admin_email']).'"';
}
$c.=' /></td>';
if (!isset($vars['online_stores_requires_login'])) {
	$vars['online_stores_requires_login']=0;
}
$c.='<th width="20%">Users must log in</th><td><input type="checkbox"'
	.' name="page_vars[online_stores_requires_login]"'
	.($vars['online_stores_requires_login']?' checked="checked"':'')
	.' /></td></tr>';
// }
// { currency
$c.='<tr><th>Currency</th><td><select name="online_store_currency">';
foreach ($online_store_currencies as $key=>$val) {
	$c.= '<option value="'.$key.'"';
	if ($key==$DBVARS['online_store_currency']) {
		$c.= ' selected="selected"';
	}
	$c.= '>'.$val[0].': '.htmlspecialchars($val[1]).'</option>';
}
$c.= '</select></td>';
// }
// { VAT
$vat=isset($vars['online_stores_vat_percent'])?$vars['online_stores_vat_percent']:'';
if ($vat=='') {
	$vat=0;
}
$c.='<th>VAT</th><td><input name="page_vars[online_stores_vat_percent]"'
	.' value="'.((float)$vat).'" /></td></tr>';
// }
// { payment types
$c.='<tr><th>Payment Types</th><td colspan="3"><div class="tabs">';
$c.='<ul>';
$c.='<li><a href="#online-store-payments-paypal">PayPal</a></li>';
$c.='<li><a href="#online-store-payments-realex">Realex</a></li>';
$c.='</ul>';
// { paypal
$c.='<div id="online-store-payments-paypal">';
$c.='<table>';
$c.='<tr><th>Email Address</th>';
$c.='<td><input type="email" name="page_vars[online_stores_paypal_address]"';
if (isset($vars['online_stores_paypal_address'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_paypal_address']).'"';
}
$c.=' /></td></tr></table></div>';
// }
// { realex
$c.='<div id="online-store-payments-realex">'
	.'<table>';
// { Merchant ID
$c.='<tr><th>Merchant ID</th><td>'
	.'<input name="page_vars[online_stores_realex_merchantid]"';
if (isset($vars['online_stores_realex_merchantid'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_realex_merchantid'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { Shared Secret
$c.='<tr><th>Shared Secret</th><td>'
	.'<input name="page_vars[online_stores_realex_sharedsecret]"';
if (isset($vars['online_stores_realex_sharedsecret'])) {
	$c.=' value="'.htmlspecialchars($vars['online_stores_realex_sharedsecret'])
		.'"';
}
$c.=' /></td></tr>';
// }
// { redirect page
$c.='<tr><th>Redirect after payment</th><td>';
$c.='<select id="online_store_redirect_to" name="page_vars[online_store_redirect_to]">';
if (isset($vars['online_store_redirect_to'])
	&& $vars['online_store_redirect_to']
) {
	$parent=Page::getInstance($vars['online_store_redirect_to']);
	$c.='<option value="'.$parent->id.'">'.htmlspecialchars($parent->name).'</option>';
}
else{
	$vars['online_store_redirect_to']=0;
	$c.='<option value="0"> -- none -- </option>';
}
$c.='</select></td></tr>';
// }
// { test mode
$c.='<tr><th>Mode</th><td>'
	.'<select name="page_vars[online_stores_realex_testmode]">'
	.'<option value="test">Test Mode</option>'
	.'<option value="live"';
if(
	isset($vars['online_stores_realex_testmode'])
	&& $vars['online_stores_realex_testmode']=='live'
){
	$c.=' selected="selected"';
}
$c.='>Live</option></select>'
	.'In test mode, you can use the realex payment method by adding "?testmode=1"'
	.' to the URL.</td></tr>';
// }
// { note
$c.='<tr><td colspan="2">Note that some manual configuration is necessary. '
	.'You will need to provide RealEx with a template (see their Real Auth '
	.'Developers Guide for an example), and with the following Response Script'
	.'URL: <br />'
	.'http://'.$_SERVER['HTTP_HOST'].'/ww.plugins/online-store/verify/realex.php'
	.'</td></tr>';
// }
$c.=' </table></div>';
// }
$c.='</div></td></tr>';
// }
$c.='</table></div>';
// }
// { countries todo
$c.='<div id="online-store-countries">';
$c.='todo';
$c.='</div>';
// }
$c.='</div>';
WW_addScript('/ww.plugins/online-store/admin/index.js');

if (!file_exists(USERBASE.'ww.cache/online-store')) {
	mkdir(USERBASE.'ww.cache/online-store');
}
if (file_exists(USERBASE.'ww.cache/online-store/'.$page['id'])) {
	unlink(USERBASE.'ww.cache/online-store/'.$page['id']);
}
file_put_contents(
	USERBASE.'ww.cache/online-store/'.$page['id'],
	$vars['online_stores_invoice']
);
$c.='<style>@import "/ww.plugins/online-store/admin/styles.css";</style>';
