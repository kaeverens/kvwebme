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
	if ($unused_uri=='') {
		$cid=0;
	}
	// { categories
	$html='<div id="classifiedads-wrapper">';
	// { breadcrumbs
	// }
	// { sub-categories
	$subcats=dbAll(
		'select id,name from classifiedads_categories where parent='.$cid
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
	$html.='</div>';
	// }
	$html.=@$PAGEDATA->vars['footer'];
	return $html;
}

// }
