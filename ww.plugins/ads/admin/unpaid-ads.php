<?php

$sql='select id,user_id,type_id,days from ads_purchase_orders';
$ads=dbAll($sql);

$ad_types=array();
$rs=dbAll('select id,name from ads_types');
foreach ($rs as $r) {
	$ad_types[$r['id']]=$r['name'];
}

echo '<table id="ads-table"><thead><tr><th>ID</th><th>Owner</th><th>Type</th>'
	.'<th>Days</th><th></th></tr></thead><tbody>';
foreach ($ads as $ad) {
	$username='';
	if ($ad['user_id']) {
		$user=User::getInstance($ad['user_id']);
		$username=$user?$user->get('name'):'UNKNOWN';
	}
	echo '<tr id="ad-'.$ad['id'].'">'
		.'<td>'.str_pad($ad['id'], 4, '0', STR_PAD_LEFT).'</td>'
		.'<td>'.htmlspecialchars($username).'</td>'
		.'<td>'.htmlspecialchars($ad_types[$ad['type_id']]).'</td>'
		.'<td>'.$ad['days'].'</td>'
		.'<td><a href="#" class="mark-as-purchased">Mark as Purchased</a>'
	.' | <a href="#" class="delete">[x]</a></td></tr>';
}
echo '</tbody></table>';

WW_addScript('/ww.plugins/ads/admin/unpaid-ads.js');
