<?php
/**
  * Site options for the Online Store
  *
  * PHP Version 5
  *
  * @category   OnlineStorePlugin
  * @package    WebWorksWebme
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

if ( isset($_REQUEST['action']) && $_REQUEST['action']=='Save') {
	$curs=array();
	foreach($_REQUEST['os-currencies_iso'] as $key=>$val) {
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
echo '<table>'
	,'<tr><th>Currencies</th><td id="currencies">'
	.'<p>The top row is the default currency of the website.'
	.' To change the default, please drag a different row to the top.</p>'
	,'</td></tr>'
	,'</table>';
echo '<input type="submit" name="action" value="Save" /></form>';
WW_addScript('/ww.plugins/online-store/admin/site-options.js');
WW_addInlineScript('window.os_currencies='.$os_currencies.';');
WW_addCSS('/ww.plugins/online-store/admin/site-options.css');
