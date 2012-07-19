<?php

// { config

$plugin=array(
	'admin' => array( // {
		'page_type' => 'Ads_admin',
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
		'page_type' => 'Ads_frontend',
		'widget' => 'Ads_widget'
	),
	'version'=>5
);

// }

// { Ads_widget

/**
	* show ads
	*
	* @return ads HTML
	*/
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
		dbQuery(
			'insert into ads_track set ad_id='.$ad['id'].', view=1, cdate=now()'
		);
	}
	$html.='</div>';
	WW_addScript('ads/j/js.js');
	return $html;
}

// }
// { Ads_frontend

/**
	* show the purchase page for Ads
	*
	* @param $PAGEDATA object the page object
	*
	* @return string
	*/
function Ads_frontend($PAGEDATA) {
	if (!isset($_SESSION['userdata']['id'])) {
		return $PAGEDATA->render()
			.'<p>'.__('You must be logged in to use this page.').'</p>'
			.'<p><a href="/_r?type=login">'.__('Login').'</a> '.__('or')
			.' <a href="/_r?type=register">'.__('Register').'</a></p>';
	}
	$html='<div id="ads-purchase-wrapper"></div>';
	WW_addInlineScript(
		'var ads_paypal="'.addslashes($PAGEDATA->vars['ads-paypal']).'";'
	);
	WW_addScript('ads/j/purchase.js');
	WW_addScript('/j/uploader.js');
	return $PAGEDATA->render().$html.@$PAGEDATA->vars['footer'];
}

// }
function Ads_admin($page, $vars) {
	require SCRIPTBASE.'ww.plugins/ads/admin/page-type.php';
	return $c;
}
