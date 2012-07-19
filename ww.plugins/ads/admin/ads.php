<?php

$ads=dbAll(
	'select id,name,customer_id,views,clicks,is_active,image_url,date_expire'
	.',type_id'
	.' from ads'
);

$ad_types=array();
$rs=dbAll('select id,name from ads_types');
foreach ($rs as $r) {
	$ad_types[$r['id']]=$r['name'];
}

echo '<table id="ads-table"><thead><tr><th>Name</th><th>Type</th>'
	.'<th>Owner</th><th>Views</th><th>Clicks</th><th>Active</th>'
	.'<th>Expires</th><th></th></tr></thead><tbody>';
foreach ($ads as $ad) {
	$username='';
	if ($ad['customer_id']) {
		$user=User::getInstance($ad['customer_id']);
		$username=$user->get('name');
	}
	echo '<tr id="ad-'.$ad['id'].'"><td>'.htmlspecialchars($ad['name']).'</td>'
		.'<td>'.htmlspecialchars($ad_types[$ad['type_id']]).'</td>'
		.'<td>'.htmlspecialchars($username).'</td>'
		.'<td>'.$ad['views'].'</td>'
		.'<td>'.$ad['clicks'].'</td>'
		.'<td>'.($ad['is_active']?'Yes':'No').'</td>'
		.'<td>'.$ad['date_expire'].'</td>'
		.'<td><a href="#" class="edit">edit</a>'
	.' | <a href="#" class="delete">[x]</a></td></tr>';
}
echo '</tbody></table><button class="new-ad">New Ad</button>';

WW_addScript('/ww.plugins/ads/admin.js');
