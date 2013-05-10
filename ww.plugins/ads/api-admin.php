<?php
/**
	* ads api functions
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { Ads_adminTypesDelete

/**
	* delete an ad type
	*
	* @return null
	*/
function Ads_adminTypesDelete() {
	$id=(int)$_REQUEST['id'];
	$ads=dbOne('select count(id) ids from ads where type_id='.$id, 'ids');
	if ($ads) {
		return array(
			'error'=>'cannot delete this Ad Type because there are Ads using it'
		);
	}
	dbQuery('delete from ads_types where id='.$id);
	return array(
		'ok'=>1
	);
}

// }
// { Ads_adminAdDelete

/**
	* delete an ad
	*
	* @return null
	*/
function Ads_adminAdDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from ads where id='.$id);
	return array(
		'ok'=>1
	);
}

// }
// { Ads_adminTypesEdit

/**
	* edit an ad type
	*
	* @return null
	*/
function Ads_adminTypesEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$width=(int)$_REQUEST['width'];
	$height=(int)$_REQUEST['height'];
	$price_per_day=(float)$_REQUEST['price_per_day'];
	$sql='ads_types set name="'.addslashes($name).'", width='.$width
		.', height='.$height.', price_per_day='.$price_per_day;
	if ($id) {
		dbQuery('update '.$sql.' where id='.$id);
	}
	else {
		dbQuery('insert into '.$sql);
	}
}

// }
// { Ads_adminAdGet

/**
	* Ads_adminAdGet
	*
	* @return array ad details
	*/
function Ads_adminAdGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from ads where id='.$id);
}

// }
// { Ads_adminTypesList

/**
	* get list of ad types
	*
	* @return array of ad types
	*/
function Ads_adminTypesList() {
	$adTypes=array();
	$rs=dbAll('select id,name from ads_types order by name');
	foreach ($rs as $r) {
		$adTypes[$r['id']]=$r['name'];
	}
	return $adTypes;
}

// }
// { Ads_adminAdEdit

/**
	* edit an ad
	*
	* @return null
	*/
function Ads_adminAdEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$type_id=(int)$_REQUEST['type_id'];
	$customer_id=(int)$_REQUEST['customer_id'];
	$is_active=(int)$_REQUEST['is_active'];
	$date_expire=$_REQUEST['date_expire'];
	$target_url=$_REQUEST['target_url'];
	$image_url=$_REQUEST['image_url'];
	$sql='ads set name="'.addslashes($name).'", type_id='.$type_id
		.', customer_id='.$customer_id.', is_active='.$is_active
		.', date_expire="'.addslashes($date_expire).'"'
		.', target_url="'.addslashes($target_url).'"'
		.', image_url="'.addslashes($image_url).'"';
	if ($id) {
		dbQuery('update '.$sql.' where id='.$id);
	}
	else {
		dbQuery('insert into '.$sql.', cdate=now()');
		$id=dbLastInsertId();
		if (strpos($image_url, '/f/ads/0/')!==false) {
			$fname=str_replace('/f/ads/0/', '', $image_url);
			@mkdir(USERBASE.'/f/ads/'.$id, 0777, true);
//			echo USERBASE.'/f/ads/0/'.$fname."\n".USERBASE.'/f/ads/'.$id.'/'.$fname;
			rename(USERBASE.'/f/ads/0/'.$fname, USERBASE.'/f/ads/'.$id.'/'.$fname);
			$sql='update ads set image_url="/f/ads/'.$id.'/'.addslashes($fname).'" where id='.$id;
			dbQuery($sql);
		}
	}
}

// }
function Ads_adminImageUpload() {
	$id=(int)$_REQUEST['id'];
	@mkdir(USERBASE.'/f/ads/'.$id, 0777, true);
	$imgs=new DirectoryIterator(USERBASE.'/f/ads/'.$id);
	foreach ($imgs as $img) {
		if ($img->isDot()) {
			continue;
		}
		unlink($img->getPathname());
	}
	$from=$_FILES['Filedata']['tmp_name'];
	$to=USERBASE.'/f/ads/'.$id.'/'.$_FILES['Filedata']['name'];
	move_uploaded_file($from, $to);
	Core_cacheClear('ads');
	return array(
		'url'=>'/f/ads/'.$id.'/'.$_FILES['Filedata']['name']
	);
}
function Ads_adminTrackSummarise() {
	$timeout=5; // how many seconds to allow this to run
	dbQuery('delete from ads_track where to_delete=1');
	$dates=dbAll('select count(ad_id) as ads,date(cdate) as d from ads_track group by d order by d desc');
	$time=time();
	foreach ($dates as $d) {
		$now=time();
		if ($now-$time>$timeout) {
			continue;
		}
		$date=$d['d'];
		$sql='select count(ad_id) as cnt, ad_id,sum(click) as clicks, sum(view) as views from ads_track'
			.' where cdate>="'.$date.'" and cdate<"'.$date.' 24" and to_delete=0 group by ad_id';
		$ad_data=dbAll($sql);
		foreach ($ad_data as $i) {
			$now=time();
			if ($now-$time>$timeout) {
				continue;
			}
			if ($i['cnt']!='1') {
				dbQuery(
					'update ads_track set to_delete=1 where cdate>="'.$date.'" and cdate<"'.$date.' 24"'
					.' and ad_id='.$i['ad_id']
				);
				$sql='insert into ads_track set cdate="'.$date.'", ad_id='.$i['ad_id'].', click='.$i['clicks']
					.', view='.$i['views'];
				dbQuery($sql);
			}
		}
	}
	dbQuery('delete from ads_track where to_delete=1');
	$ads=dbAll('select sum(click) as clicks, sum(view) as views, ad_id from ads_track group by ad_id');
	foreach ($ads as $ad) {
		dbQuery('update ads set clicks='.$ad['clicks'].', views='.$ad['views'].' where id='.$ad['ad_id']);
	}
}
