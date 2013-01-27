<?php
/**
	* plugin page for classified ads
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { config

$plugin=array(
	'admin' => array( // {
		'page_type' => 'ClassifiedAds_admin'
	), // }
	'description'=>function() {
		return __('Classified Ads');
	},
	'name' => function() {
		return __('Classified Ads');
	},
	'frontend'=>array( // { frontend
		'page_type' => 'ClassifiedAds_frontend'
	), // }
	'version'=>4
);

// }

// { ClassifiedAds_admin

/**
	* page type admin
	*
	* @param object $page the page
	* @param array  $vars any variables
	*
	* @return html
	*/
function ClassifiedAds_admin($page, $vars) {
	require SCRIPTBASE.'ww.plugins/classified-ads/admin/page-type.php';
	return $c;
}

// }
// { ClassifiedAds_frontend

/**
	* frontend of the classified ads thing
	*
	* @param object $PAGEDATA the page object
	*
	* @return html
	*/
function ClassifiedAds_frontend($PAGEDATA) {
	global $unused_uri;
	$html=$PAGEDATA->render();
	$bits=false;
	if ($unused_uri=='') {
		$cid=0;
	}
	else {
		$bits=explode('/', preg_replace('/\/$/', '', $unused_uri));
		$cid=ClassifiedAds_getCategoryId($bits);
	}
	$html='<div id="classifiedads-wrapper">';
	// { categories
	// { breadcrumbs
	if ($bits) {
		$html.='<div class="breadcrumbs">'
			.ClassifiedAds_getBreadcrumbs($PAGEDATA, $bits).'</div>';
	}
	// }
	// { sub-categories
	$subcats=dbAll(
		'select id, icon, name from classifiedads_categories where parent='.$cid
		.' order by name'
	);
	if (count($subcats)) {
		$html.='<div id="classifiedads-subcats">'
			.'<h2>Categories</h2><ul>';
		foreach ($subcats as $cat) {
			$html.='<li>'
				.'<a href="'.$_SERVER['REQUEST_URI'].'/'
				.htmlspecialchars($cat['name']).'">';
			if ($cat['icon']) {
				$html.='<img src="/a/f=getImg/'.$cat['icon'].'/w=32/h=32"/>';
			}
			$html.=htmlspecialchars($cat['name']);
			$html.='</a></li>';
		}
		$html.='</div>';
	}
	// }
	// }
	// { ads
	$subcatsRecursive=ClassifiedAds_getCategoryIdsRecursive($cid);
	$ads=dbAll(
		'select id, title, cost, location, excerpt, creation_date'
		.' from classifiedads_ad'
		.' where category_id in ('.join(', ', $subcatsRecursive).')'
		.' order by creation_date desc limit 100'
	);
	$html.='<table id="classifiedads-ads">'
		.'<thead><tr><th colspan="2">Title</th><th>Location</th><th>Posted</th><th>Price</th>'
		.'</tr></thead>'
		.'<tbody>';
	foreach ($ads as $ad) {
		$html.='<tr class="ad-top-details"><td rowspan="2">' // img
			.'</td><td><a href="#">'.htmlspecialchars($ad['title']).'</a></td>'
			.'<td class="location">'.htmlspecialchars($ad['location']).'</td>'
			.'<td class="posted">'.Core_dateM2H($ad['creation_date']).'</td>'
			.'<td class="price">€'.htmlspecialchars($ad['cost']).'</td>'
			.'</tr><tr class="ad-bottom-details">'
			.'<td colspan="4">'.$ad['excerpt'].'</td></tr>';
	}
	$html.='</tbody></table>';
	// }
	$html.='</div>';
	$html.=@$PAGEDATA->vars['footer'];
	return $html;
}

// }
// { ClassifiedAds_getCategoryId

/**
	* get ID of category
	*
	* @param array $bits exploded name
	*
	* return int
	*/
function ClassifiedAds_getCategoryId($bits) {
	$cbits=count($bits);
	if ($cbits==0) {
		return 0;
	}
	$pid=ClassifiedAds_getCategoryId(array_slice($bits, 0, $cbits-1));
	return (int)dbOne(
		'select id from classifiedads_categories'
		.' where name="'.addslashes($bits[$cbits-1]).'" and parent='.$pid,
		'id'
	);
}

// }
// { ClassifiedAds_getBreadcrumbs

/**
	* get breadcrumbs for category
	*
	* @param object $PAGEDATA the page object
	* @param array $bits exploded name
	*
	* return int
	*/
function ClassifiedAds_getBreadcrumbs(&$PAGEDATA, $bits) {
	$cbits=count($bits);
	if ($cbits==0) {
		return Template_breadcrumbs($PAGEDATA->id, 0);
	}
	$link=ClassifiedAds_getBreadcrumbs(
		$PAGEDATA, array_slice($bits, 0, $cbits-1)
	);
	return $link.' <span class="divider">&raquo;</span>'
		.' <a href="'.$PAGEDATA->getRelativeUrl()
		.'/'.htmlspecialchars(join('/', $bits)).'">'
		.htmlspecialchars($bits[$cbits-1]).'</a>';
}

// }
// { ClassifiedAds_getCategoryIdsRecursive

/**
	* get recursive list of categories located within this one
	*
	* @param int $cid id of this category
	*
	* @return array
	*/
function ClassifiedAds_getCategoryIdsRecursive($cid) {
	$arr=array((int)$cid);
	$cats=dbAll('select id from classifiedads_categories where parent='.$cid);
	foreach ($cats as $c) {
		$arr=array_merge($arr, ClassifiedAds_getCategoryIdsRecursive($c['id']));
	}
	return $arr;
}

// }
