<?php

$ads=dbAll('select id,name,width,height,price_per_day from ads_types');

echo '<table id="ads-types-table"><thead><tr><th>Name</th><th>Width</th><th>Height</th><th>Daily Price</th><th></th></tr></thead><tbody>';

foreach ($ads as $ad) {
	echo '<tr id="ad-type-'.$ad['id'].'"><td>'.htmlspecialchars($ad['name']).'</td>'
		.'<td>'.htmlspecialchars($ad['width']).'</td>'
		.'<td>'.htmlspecialchars($ad['height']).'</td>'
		.'<td>'.htmlspecialchars($ad['price_per_day']).'</td>'
		.'<td><a href="#" class="edit">edit</a>'
		.' | <a href="#" class="delete">[x]</a></td></tr>';
}
echo '</tbody></table><button class="new-ads-type">'.__('New Ads Type').'</button>';

WW_addScript('/ww.plugins/ads/admin.js');
