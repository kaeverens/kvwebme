<?php
/**
  * options for Online Store eBay integration
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
	foreach ($_REQUEST['ebayVals'] as $k=>$v) {
		dbQuery(
			'delete from online_store_vars where name="ebay_'.addslashes($k).'"'
		);
		dbQuery(
			'insert into online_store_vars set name="ebay_'.addslashes($k).'"'
			.', val="'.addslashes($v).'"'
		);
	}
	// }
	Core_cacheClear('online-store');
	echo '<em>Saved</em>';
}

echo '<form method="post" action="'.$_url.'" />'
	.'<div class="accordion">';
// { main
echo '<h2>'.__('Main Details').'</h2><div><table>'
	// { paypal address
	.'<tr><th>What paypal address to use</th>'
	.'<td><input name="ebayVals[paypal_address]" value="'.htmlspecialchars(dbOne(
		'select val from online_store_vars where name="ebay_paypal_address"', 'val'
	)).'"/></td></tr>'
	// }
	// { status
	.'<tr><th>Status</th><td><select name="ebayVals[status]">'
	.'<option value="0">Sandbox</option>'
	.'<option value="1"'
	.(dbOne('select val from online_store_vars where name="ebay_status"', 'val')?' selected="selected"':'')
	.'>Production</option>'
	.'</select></td></tr>'
	// }
	// { country from
	.'<tr><th>What country your products come from (2-letter code)</th>'
	.'<td><input name="ebayVals[country_from]" value="'.htmlspecialchars(dbOne(
		'select val from online_store_vars where name="ebay_country_from"', 'val'
	)).'"/></td></tr>'
	// }
	// { location
	.'<tr><th>What location in the country?</th>'
	.'<td><input name="ebayVals[location]" value="'.htmlspecialchars(dbOne(
		'select val from online_store_vars where name="ebay_location"', 'val'
	)).'"/></td></tr>'
	// }
	// { dispatch days
	.'<tr><th>How many days to dispatch</th>'
	.'<td><input name="ebayVals[dispatch_days]" value="'.htmlspecialchars(dbOne(
		'select val from online_store_vars where name="ebay_dispatch_days"', 'val'
	)).'"/></td></tr>'
	// }
	.'</table></div>';
// }
// { sandbox authentication
echo '<h2>'.__('Sandbox Authentication').'</h2><div>'
	.'<p>You must get a developer account from'
	.' <a href="https://developer.ebay.com/">eBay</a>.</p>'
	.'<table>'
	// { devid
	.'<tr><th>Dev ID</th><td><input name="ebayVals[sandbox_devid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_sandbox_devid"', 'val'
		)
	)
	.'"/></td>'
	// }
	// { user token
	.'<th rowspan="3">User Token</th><td rowspan="3">'
	.'<textarea name="ebayVals[sandbox_usertoken]">'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_sandbox_usertoken"',
			'val'
		)
	)
	.'</textarea></td></tr>'
	// }
	// { appid
	.'<tr><th>App ID</th><td><input name="ebayVals[sandbox_appid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_sandbox_appid"', 'val'
		)
	)
	.'"/></td>'
	// }
	// { certid
	.'<tr><th>Cert ID</th><td><input name="ebayVals[sandbox_certid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_sandbox_certid"',
			'val'
		)
	)
	.'"/></td></tr>'
	// }
	.'</table></div>';
// }
// { production authentication
echo '<h2>'.__('Production Authentication').'</h2><div>'
	.'<p>You must get a developer account from'
	.' <a href="https://developer.ebay.com/">eBay</a>.</p><table>'
	// { devid
	.'<tr><th>Dev ID</th><td><input name="ebayVals[devid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_devid"', 'val'
		)
	)
	.'"/></td>'
	// }
	// { user token
	.'<th rowspan="3">User Token</th><td rowspan="3">'
	.'<textarea name="ebayVals[usertoken]">'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_usertoken"',
			'val'
		)
	)
	.'</textarea></td></tr>'
	// }
	// { appid
	.'<tr><th>App ID</th><td><input name="ebayVals[appid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_appid"', 'val'
		)
	)
	.'"/></td></tr>'
	// }
	// { certid
	.'<tr><th>Cert ID</th><td><input name="ebayVals[certid]" value="'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_certid"',
			'val'
		)
	)
	.'"/></td></tr>'
	// }
	.'</table></div>';
// }
// { description afterword
echo '<h2>'.__('Description Afterword').'</h2><div><table>'
	// {
	.'<tr><textarea name="ebayVals[description_afterword]">'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_description_afterword"',
			'val'
		)
	)
	.'</textarea></td></tr>'
	// }
	.'</table></div>';
// }
// { returns policy
echo '<h2>'.__('Returns Policy').'</h2><div><table>'
	// { user token
	.'<tr><textarea name="ebayVals[returns_policy]">'
	.htmlspecialchars(
		dbOne(
			'select val from online_store_vars where name="ebay_returns_policy"',
			'val'
		)
	)
	.'</textarea></td></tr>'
	// }
	.'</table></div>';
// }
echo '</div>';
echo '<input type="submit" name="action" value="Save" /></form>';
WW_addScript('online-store-ebay/admin/options.js');
WW_addCSS('/ww.plugins/online-store-ebay/admin/options.css');
