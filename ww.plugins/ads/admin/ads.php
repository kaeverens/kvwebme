<?php

$ads=dbAll('select id,name,customer_id,views,clicks,is_active,image_url from ads');

echo '<table id="ads-table"><thead><tr><th>Name</th><th>Customer</th><th>Views</th><th>Clicks</th><th>Active</th><th></th></tr></thead><tbody>';

foreach ($ads as $ad) {
	echo '<tr id="ad-'.$ad['id'].'"><td>'.htmlspecialchars($ad['name']).'</td>'
		.'<td>'.htmlspecialchars($ad['customer']).'</td>'
		.'<td>'.$ad['views'].'</td>'
		.'<td>'.$ad['clicks'].'</td>'
		.'<td>'.($ad['is_active']?'Yes':'No').'</td>'
		.'<td></td></tr>';
}

WW_addScript('/ww.plugins/ads/admin.js');
