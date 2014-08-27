<?php

$types=dbAll('select id, name, width, height from ads_types');

$html.='<div class="ads-show-all">';
foreach ($types as $type) {
	$html.='<h2>'.htmlspecialchars($type['name']).'</h2>';
	$ads=dbAll('select id, image_url, cdate, target_type from ads where type_id='.$type['id'].' and is_active order by name');
	foreach ($ads as $ad) {
		$html.=Ads_adShow($ad, $type);
	}
}
$html.='</div>';
