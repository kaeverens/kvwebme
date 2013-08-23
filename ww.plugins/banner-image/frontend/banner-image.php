<?php
/*
	Webme Banner Image Plugin v0.1
	File: frontend/banner-image.php
	Developers:
		Conor Mac Aoidh  http://macaoidh.name/
		Kae Verens       http://verens.com/
	Report Bugs: kae@verens.com
*/

function show_banner($vars) {
	$banner=false;
	if (!is_array($vars) && @$vars->id) {
		$b=Core_cacheLoad('banner-images', 'id'.$vars->id);
		if ($b===false) {
			$b=dbRow('select * from banners_images where id='.$vars->id);
			if ($b && count($b) && !$b['html']) {
				$b['html']=BannerImage_getImgHtml($vars->id);
				dbQuery(
					'update banners_pages set html="'.addslashes($b['html'])
					.'" where id='.$vars->id
				);
			}
			Core_cacheSave('banner-images', 'id'.$vars->id, $b);
		}
	}
	elseif($GLOBALS['PAGEDATA']->id) {
		$b=Core_cacheLoad('banner-images', 'bypage'.$GLOBALS['PAGEDATA']->id);
		if ($b===false) {
			$b=dbAll(
				'select * from banners_pages,banners_images where pageid='
				.$GLOBALS['PAGEDATA']->id.' and bannerid=id'
			);
			Core_cacheSave('banner-images', 'bypage'.$GLOBALS['PAGEDATA']->id, $b);
		}
		$i=rand(0, count($b)-1);
		$b=isset($b[$i])?$b[$i]:false;
		if ($b && count($b) && !$b['html']) {
			$b['html']=BannerImage_getImgHtml($b['id']);
			dbQuery(
				'update banners_pages set html="'.addslashes($b['html'])
				.'" where id='.$b['id']
			);
		}
	}
	if (!isset($b) || $b===false || !count($b)) {
		$b=Core_cacheLoad('banner-image', 'all');
		if ($b===false) {
			$b=dbAll('select * from banners_images');
			Core_cacheSave('banner-image', 'all', $b);
		}
		$i=rand(0, count($b)-1);
		$b=isset($b[$i])?$b[$i]:false;
	}
	if ($b && count($b)) {
		$banner=$b['html'];
		if (!$banner) {
			$banner=BannerImage_getImgHtml($vars->id);
		}
	}
	if (!$banner) {
		if (is_array($vars) && @$vars['default']) {
			$banner=$vars['default'];
		}
		else {
			$banner='';
		}
	}
	if (!$banner) {
		return '';
	}
	return '<style type="text/css">#banner{background:none}</style>'.$banner;
}
