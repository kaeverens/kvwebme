<?php
$kfm_do_not_save_session=true;
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
function image_gallery_show($PAGEDATA){
	$vars=$PAGEDATA->vars;
	// {
	$c=$PAGEDATA->render();
	$start=getVar('start');
	if(!$start)$start=0;
	$vars=array_merge(array(
		'image_gallery_directory'    =>'/',
		'image_gallery_x'            =>3,
		'image_gallery_y'            =>2,
		'image_gallery_thumbsize'    =>150,
		'image_gallery_captionlength'=>100,
		'image_gallery_type'         =>'simple gallery',
		'image_gallery_forsale'      =>false
	),$vars);
	$imagesPerPage=$vars['image_gallery_x']*$vars['image_gallery_y'];
	// }
	$dir_id=kfm_api_getDirectoryId(preg_replace('/^\//','',$vars['image_gallery_directory']));
	$images=kfm_loadFiles($dir_id);
	$images=$images['files'];
	$n=count($images);
	if (!isset($vars['footer'])) {
		$vars['footer']='';
	}
	if($n){
		switch($vars['image_gallery_type']){
			case 'ad-gallery':
				require dirname(__FILE__).'/gallery-type-ad.php';
				break;
			default:
				require dirname(__FILE__).'/gallery-type-simple.php';
		}
		if($vars['image_gallery_forsale']){
			$prices=array();
			for($i=0;isset($vars['image_gallery_prices_'.$i]);++$i){
				$price=(float)preg_replace('/[^0-9.]/','',$vars['image_gallery_prices_'.$i]);
				if(!$price)continue;
				$prices[]=array(
					$vars['image_gallery_pricedescs_'.$i],
					$price
				);
			}
			$c.='<script>var ig_prices='.json_encode($prices).';</script>';
			WW_addScript('/ww.plugins/image-gallery/j/online-store.js');
		}
		return $c.$vars['footer'];
	}
	else{
		return $c.'<em>gallery "'.$vars['image_gallery_directory'].'" not found.</em>'.$vars['footer'];
	}
}
