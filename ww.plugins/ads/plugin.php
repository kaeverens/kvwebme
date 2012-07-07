<?php
$plugin=array(
	'admin' => array( // {
		'menu' => array(
			'Ads>Ads'=>
				'/ww.admin/plugin.php?_plugin=ads&amp;_page=ads',
			'Ads>Ad Types'=>
				'/ww.admin/plugin.php?_plugin=ads&amp;_page=ad-types'
		),
		'widget' => array(
			'form_url'   => '/ww.plugins/ads/admin/widget.php'
		)
	), // }
	'description'=>function() {
		return __('Add ads. Record each click and view.');
	},
	'name' => function() {
		return __('Ads');
	},
	'frontend'=>array(
		'widget' => 'Ads_widget',
	),
	'version'=>2
);

function Ads_widget($params) {
	$type_id=(int)$params->{'ad-type'};
	$howmany=(int)$params->{'how-many'};
	$ads=dbAll(
		'select id,image_url from ads where type_id='.$type_id.' order by rand()'
		.' limit '.$howmany
	);
	$html='<div class="ads-wrapper type-'.$type_id.'">';
	foreach ($ads as $ad) {
		$html.='<div class="ads-ad" data-id="'.$ad['id'].'">'
			.'<img src="'.$ad['image_url'].'"/>'
			.'</div>';
	}
	$html.='</div>';
	return $html;
}
