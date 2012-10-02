<?php
/**
  * Site options for the Online Store
  *
  * PHP Version 5
  *
  * @category None
  * @package  None
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     www.kvweb.me
 */

if ( isset($_REQUEST['action']) && $_REQUEST['action']=='Save') {
	// { online_store_vars
	foreach ($_REQUEST['online_store_vars'] as $k=>$v) {
		dbQuery('delete from online_store_vars where name="'.addslashes($k).'"');
		dbQuery(
			'insert into online_store_vars set name="'.addslashes($k).'"'
			.', val="'.addslashes($v).'"'
		);
	}
	$_SESSION['onlinestore_prices_shown_post_vat']=(int)@$_REQUEST['vat_display'];
	// }
	// { currencies
	$curs=array();
	foreach ($_REQUEST['os-currencies_iso'] as $key=>$val) {
		$curs[]=array(
			'name'=>$_REQUEST['os-currencies_name'][$key],
			'iso'=>$_REQUEST['os-currencies_iso'][$key],
			'symbol'=>$_REQUEST['os-currencies_symbol'][$key],
			'value'=>$_REQUEST['os-currencies_value'][$key]
		);
	}
	$curs=json_encode($curs);
	dbQuery('delete from site_vars where name="currencies"');
	dbQuery(
		'insert into site_vars set name="currencies",value="'
		.addslashes($curs).'"'
	);
	// }
	// { group discounts
	foreach ($_REQUEST['discounts'] as $gid=>$val) {
		$val=(float)$val;
		$gid=(int)$gid;
		$group=dbRow('select meta from groups where id='.$gid);
		if ($group) {
			if ($group['meta']=='') {
				$group['meta']='{}';
			}
			$meta=json_decode($group['meta'], true);
			$meta['discount']=$val;
			dbQuery(
				'update groups set meta="'.addslashes(json_encode($meta))
				.'" where id='.$gid
			);
		}
	}
	// }
	Core_cacheClear('online-store');
	echo '<em>Saved</em>';
}

$os_currencies=dbOne(
	'select value from site_vars where name="currencies"',
	'value'
);
if (!$os_currencies) {
	$os_currencies='[{"name":"Euro","iso":"Eur","symbol":"€","value":1}]';
}
echo '<form method="post" action="'.$_url.'" />'
	.'<div class="accordion">';
// { default price display with/without VAT
echo '<h2><a href="#">'.__('VAT').'</a></h2><div>'
	.'<p>'.__('Prices will be displayed on the frontend by default:')
	.'<select name="online_store_vars[vat_display]">'
	.'<option value="0">'.__('pre-VAT').'</option>'
	.'<option value="1"';
$postvat=(int)dbOne(
	'select val from online_store_vars where name="vat_display"',
	'val'
);
if ($postvat) {
	echo ' selected="selected"';
}
echo '>'.__('post-VAT').'</option></select>.</p></div>';
// }
// { invoices
echo '<h2><a href="#">'.__('Invoices').'</a></h2>'
	.'<div id="invoices">'
	.'<select name="online_store_vars[invoices_by_email]">';
$opts=array(
	__('Invoice should be emailed to customer when the order is Paid'),
	__('Invoice should not be emailed at all'),
	__(
		'Invoice should be emailed to customer when the order is Delivered'
	),
	__('Invoice should be emailed when the payment is Authorised')
);
$curval=(int)dbOne(
	'select value from online_store_vars where name="invoices_by_email"',
	'value'
);
foreach ($opts as $k=>$opt) {
	echo '<option value="'.$k.'"';
	if ($k==$curval) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars(__($opt)).'</option>';
}
echo '</select></div>';
// }
// { exports
echo '<h2><a href="#">'.__('Automated Exports').'</a></h2>'
	.'<div id="exports">'
	.'<table>';
// { export at what point
echo '<tr><th>'.__('Export at what point').'</th><td>'
	.'<select name="online_store_vars[export_at_what_point]">';
// TODO: Translate
$opts=array(
	'Export details to file when the order is Paid',
	'Do not export details to file at all',
	'Export details to file when the order is Paid And Delivered'
);
$curval=(int)dbOne(
	'select val from online_store_vars where name="export_at_what_point"',
	'val'
);
foreach ($opts as $k=>$opt) {
	echo '<option value="'.$k.'"';
	if ($k==$curval) {
		echo ' selected="selected"';
	}
	echo '>'.htmlspecialchars(__($opt)).'</option>';
}
echo '</select></td></tr>';
// }
// { Orders Directory
echo '<tr><th>'.__('Orders Directory').'</th>'
	.'<td><input name="online_store_vars[export_dir]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="export_dir"',
			'val'
		)
	)
	.'" placeholder="'
	.'/f/orders"/></td></tr>';
// }
// { Customers Directory
echo '<tr><th>'.__('Customers Directory').'</th><td>'
	.'<input name="online_store_vars[export_customers]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="export_customers"',
			'val'
		)
	)
	.'" placeholder="'
	.'/f/customers"/></td></td></tr>';
// }
// { Customers Filename
echo '<tr><th>'.__('Customers Filename').'</th>'
	.'<td><input name="online_store_vars[export_customer_filename]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars'
			.' where name="export_customer_filename"',
			'val'
		)
	)
	.'"'
	.' placeholder="customer-{{$Email}}.csv"/></td></tr>';
// }
echo '</table></div>';
// }
// { currencies
echo '<h2><a href="#">'.__('Currencies').'</a></h2>'
	.'<div id="currencies">'
	.'<p>'.__('The top row is the default currency of the website.'
	.' To change the default, please drag a different row to the top.').'</p>'
	.'</div>';
// }
// { discounts
echo '<h2><a href="#">'.__('Group discounts').'</a></h2><div><table>';
$groups=dbAll('select * from groups order by name');
foreach ($groups as $group) {
	if ($group['meta']=='') {
		$group['meta']='{}';
	}
	$meta=json_decode($group['meta'], true);
	echo '<tr><th>'.htmlspecialchars($group['name']).'</th><td>%'
		.'<input type="number" name="discounts['.$group['id']
		.']" min="0" max="100" value="'.((float)@$meta['discount']).'"/></td></tr>';
}
echo '</table></div>';
// }
echo '</div>'
// TODO: translation needed
	.'<input type="submit" name="action" value="Save" /></form>';
WW_addScript('online-store/admin/site-options.js');
WW_addInlineScript('window.os_currencies='.$os_currencies.';');
WW_addCSS('/ww.plugins/online-store/admin/site-options.css');
