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
		'page_type' => 'ClassifiedAds_frontend',
		'template_functions' => array( // {
			'CLASSIFIEDADS_CATEGORY_TREE' => array( // {
				'function' => 'ClassifiedAds_categoryTree'
			) // }
		) // }
	), // }
	'version'=>8
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
	$sql='select name from classifiedads_categories where id='.$cid;
	WW_addInlineScript(
		'var classifiedads_categoryId='.$cid
		.', classifiedads_categoryName="'.addslashes(
			dbOne($sql, 'name')
		).'"'
		.', classifiedads_paypal="'.$PAGEDATA->vars['classified-ads-paypal'].'";'
	);
	$html='<div id="classifiedads-wrapper">';
	// { breadcrumbs
	if ($bits) {
		$html.='<div class="breadcrumbs">'
			.ClassifiedAds_getBreadcrumbs($PAGEDATA, $bits);
		if ($cid) {
			$html.=' <span class="divider">&raquo;</span>'
				.' <button class="classifiedads-advertise-button">'
				.'Advertise Here</button>';
			WW_addScript('classified-ads/frontend/advertise.js');
		}
		$html.='</div>';
	}
	// }
	if ($bits && preg_match('/^[0-9]+-.*/', $bits[count($bits)-1])) {
		$ad_id=(int)preg_replace('/[^0-9].*/', '', $bits[count($bits)-1]);
		$ad=dbRow('select * from classifiedads_ad where id='.$ad_id.' and status');
		$html.='<div id="classifiedads-single">'
			.'<h2>'.htmlspecialchars($ad['title']).'</h2>'
			.'<table id="classifiedads-ad-details"><tr>'
			.'<td class="classifiedads-creation-date">Posted: '
			.Core_dateM2H($ad['creation_date']).'</td>'
			.'<td class="classifiedads-location">Location: '
			.htmlspecialchars($ad['location']).'</td>'
			.'<td class="classifiedads-cost">Cost: €'.htmlspecialchars($ad['cost'])
			.'</td></tr></table>';
		$images=array();
		$dir='/f/userfiles/'.$ad['user_id'].'/classified-ads/'.$ad['id'];
		if (file_exists(USERBASE.$dir)) {
			$files=new DirectoryIterator(USERBASE.$dir);
			foreach ($files as $f) {
				if ($f->isDot() || $f->isDir()) {
					continue;
				}
				$images[]='<a href="'.$dir.'/'.$f->getFilename().'" target="popup">'
					.'<img src="'.$dir.'/'.$f->getFilename().'"'
					.' style="max-width:128px;max-height:128px"/></a>';
			}
		}
		$html.='<p class="classified-ads-body">'
			.nl2br(htmlspecialchars($ad['body'])).'</p>'
			.join('', $images);
		$html.='<table class="classifiedads-contact"><tr>';
		if ($ad['phone']) {
			$html.='<td>Phone: '.htmlspecialchars($ad['phone']).'</td>';
		}
/*		if ($ad['email']) {
			$html.='<td>Email: <a href="#" class="classified-ads-email"'
				.' data-ad-id="'.$ad['id'].'">click to send</a></td>';
		} */
		$html.='</tr></table>';
		$html.='</div>';
	}
	else { // show sub-categories and products
		// { sub-categories
		$subcats=dbAll(
			'select id, icon, name from classifiedads_categories where parent='.$cid
			.' order by name'
		);
		if (count($subcats)) {
			$html.='<div id="classifiedads-subcats">'
				.'<h2>Categories</h2><ul>';
			foreach ($subcats as $cat) {
				$url=ClassifiedAds_getCategoryUrl($cat['id']);
				$html.='<li>'
					.'<a href="'.$url.'">';
				if ($cat['icon']) {
					$html.='<img src="/a/f=getImg/'.$cat['icon'].'/w=32/h=32"/>';
				}
				$html.=htmlspecialchars($cat['name']);
				$html.='</a></li>';
			}
			$html.='</div>';
		}
		// }
		// { ads
		$subcatsRecursive=ClassifiedAds_getCategoryIdsRecursive($cid);
		$ads=dbAll(
			'select id, user_id, category_id, title, cost, location, excerpt, creation_date'
			.' from classifiedads_ad'
			.' where category_id in ('.join(', ', $subcatsRecursive).')'
			.' and status'
			.' order by creation_date desc limit 100'
		);
		$html.='<table id="classifiedads-ads">'
			.'<thead><tr><th colspan="2">Title</th><th>Location</th><th>Posted</th>'
			.'<th>Price</th></tr></thead><tbody>';
		foreach ($ads as $ad) {
			$url=ClassifiedAds_getCategoryUrl($ad['category_id'])
				.'/'.$ad['id'].'-'.preg_replace('/[^a-z0-9A-Z]/', '-', $ad['title']);
			$img='';
			$adDir='/f/userfiles/'.$ad['user_id'].'/classified-ads/'.$ad['id'];
			$dir=USERBASE.$adDir;
			if (file_exists($dir)) {
				$files=new DirectoryIterator($dir);
				foreach ($files as $f) {
					if (!$f->isDot()) {
						$img='<img style="max-width:64px;max-height:64px;" src="'.$adDir.'/'.$f->getFilename().'"/>';
						break;
					}
				}
			}
			$html.='<tr class="ad-top-details"><td rowspan="2">' // img
				.$img
				.'</td><td><a href="'.$url.'">'.htmlspecialchars($ad['title']).'</a></td>'
				.'<td class="location">'.htmlspecialchars($ad['location']).'</td>'
				.'<td class="posted">'.Core_dateM2H($ad['creation_date']).'</td>'
				.'<td class="price">€'.htmlspecialchars($ad['cost']).'</td>'
				.'</tr><tr class="ad-bottom-details">'
				.'<td colspan="4">'.$ad['excerpt'].'</td></tr>';
		}
		$html.='</tbody></table>';
		// }
	}
	$html.='</div>';
	$html.=@$PAGEDATA->vars['footer'];
	WW_addCSS('/ww.plugins/classified-ads/frontend/style.css');
	WW_addScript('/j/uploader.js');
	return $html;
}

// }
// { ClassifiedAds_getCategoryId

/**
	* get ID of category
	*
	* @param array $bits exploded name
	*
	* @return int
	*/
function ClassifiedAds_getCategoryId($bits) {
	$cbits=count($bits);
	if ($cbits==0) {
		return 0;
	}
	$pid=ClassifiedAds_getCategoryId(array_slice($bits, 0, $cbits-1));
	$name=preg_replace('/[^a-zA-Z0-9]/', '_', $bits[$cbits-1]);
	$cid=(int)dbOne(
		'select id from classifiedads_categories'
		.' where name like "'.$name.'" and parent='.$pid,
		'id'
	);
	return $cid?$cid:$pid;
}

// }
// { ClassifiedAds_getBreadcrumbs

/**
	* get breadcrumbs for category
	*
	* @param object &$PAGEDATA the page object
	* @param array  $bits      exploded name
	*
	* @return int
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
// { ClassifiedAds_getCategoryUrl

/**
	* get category url
	*
	* @param int $cid category id
	*
	* @return string
	*/
function ClassifiedAds_getCategoryUrl($cid) {
	if ((int)$cid===0) {
		return PAGE::getInstanceByType('classified-ads')->getRelativeUrl();
	}
	if (!isset($GLOBALS['ClassifiedAdsUrls'])) {
		$GLOBALS['ClassifiedAdsUrls']=array();
	}
	if (isset($GLOBALS['ClassifiedAdsUrls'][$cid])) {
		return $GLOBALS['ClassifiedAdsUrls'][$cid];
	}
	$r=dbRow('select name,parent from classifiedads_categories where id='.$cid);
	$url=ClassifiedAds_getCategoryUrl($r['parent']).'/'
		.preg_replace('/[^a-z0-9A-Z]/', '-', $r['name']);
	$GLOBALS['ClassifiedAdsUrls'][$cid]=$url;
	return $url;
}

// }
// { ClassifiedAds_categoryTree

/**
	* get a tree of all classified ad categories, with links to each
	*
	* @param int $pid parent ID
	*
	* @return string
	*/
function ClassifiedAds_categoryTree($pid=0) {
	if (is_array($pid)) {
		$pid=0;
	}
	$lis=array();
	$rs=dbAll(
		'select id,icon,name from classifiedads_categories where parent='.$pid
		.' order by name'
	);
	if (count($rs)) {
		$lis[]='<ul class="classifiedads-category-tree"'
			.' data-classifiedads-category-tree="0">';
		foreach ($rs as $r) {
			$img=$r['icon']
				?'<img src="/a/f=getImg/'.$r['icon'].'/w=16/h=16"/>'
				:'';
			$lis[]='<li>'
				.'<a href="'.ClassifiedAds_getCategoryUrl($r['id']).'">'
				.'<ins>'.$img.'</ins>'
				.htmlspecialchars($r['name']).'</a>'
				.ClassifiedAds_categoryTree($r['id'])
				.'</li>';
		}
		$lis[]='</ul>';
	}
	WW_addScript('/j/jstree/jquery.jstree.js');
	WW_addScript('classified-ads/frontend/category-tree.js');
	WW_addCSS('/ww.plugins/classified-ads/frontend/category-tree.css');
	return $pid
		?join('', $lis)
		:'<div class="classifiedads-category-tree-wrapper">'.join('', $lis)
		.'</div>';
}

// }
