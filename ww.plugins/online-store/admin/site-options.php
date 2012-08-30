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
echo '<form method="post" action="'.$_url.'" />';
// { default price display with/without VAT
echo '<h3>VAT</h3>'
	.'<p>Prices will be displayed on the frontend by default: '
	.'<select name="online_store_vars[vat_display]">'
	.'<option value="0">pre-VAT</option>'
	.'<option value="1"';
$postvat=(int)dbOne(
	'select val from online_store_vars where name="vat_display"',
	'val'
);
if ($postvat) {
	echo ' selected="selected"';
}
echo '>post-VAT</option></select>.</p>';
// }
// { invoices
echo '<h3>Invoices</h3>'
	.'<div id="invoices">'
	.'<select name="online_store_vars[invoices_by_email]">';
$opts=array(
	'Invoice should be emailed to customer when the order is Paid',
	'Invoice should not be emailed at all',
	'Invoice should be emailed to customer when the order is Paid And Delivered'
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
echo '</select>';
// }
// { currencies
echo '<h3>Currencies</h3>'
	.'<div id="currencies">'
	.'<p>The top row is the default currency of the website.'
	.' To change the default, please drag a different row to the top.</p>'
	.'</div>';
// }
// { discounts
echo '<h3>Group discounts</h3><table>';
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
echo '</table>';
// }
echo '<input type="submit" name="action" value="Save" /></form>';
WW_addScript('online-store/admin/site-options.js');
WW_addInlineScript('window.os_currencies='.$os_currencies.';');
WW_addCSS('/ww.plugins/online-store/admin/site-options.css');
