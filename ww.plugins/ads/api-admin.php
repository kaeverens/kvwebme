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
	$type=(int)$_REQUEST['type'];
	$sql='ads_types set name="'.addslashes($name).'", width='.$width
		.', height='.$height.', price_per_day='.$price_per_day.', type='.$type
		.', not_for_sale='.(int)$_REQUEST['not_for_sale'];
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

function Ads_adminOrderMarkPaid() {
	$id=(int)$_REQUEST['item_number'];
	// create ad
	$data=dbRow('select * from ads_purchase_orders where id='.$id);
	if (!$data) {
		return array(
			'error'=>'no such ad'
		);
	}
	$sql='insert into ads set name="ad",customer_id='.$data['user_id']
		.',target_url="'.addslashes($data['target_url']).'",cdate=now()'
		.',target_type="'.addslashes($data['target_type']).'"'
		.',is_active=1,type_id='.$data['type_id']
		.',date_expire=date_add(now(), interval '.$data['days'].' day)';
	dbQuery($sql);
	$ad_id=dbLastInsertId();
	$type=dbRow('select * from ads_types where id='.$data['type_id']);
	// { poster 
	$url=false;
	$dirname=USERBASE.'/f/userfiles/'.$data['user_id'].'/ads-upload-poster';
	if (file_exists($dirname)) {
		$dir=new DirectoryIterator($dirname);
		foreach ($dir as $file) {
			if ($file->isDot()) {
				continue;
			}
			$url='userfiles/'.$data['user_id'].'/ads-upload-poster/'
				.$file->getFilename();
		}
	}
	$newName='/f/userfiles/'.$data['user_id'].'/ad-poster-'.$ad_id.'.'
		.preg_replace('/.*\./', '', $url);
	if ($url) {
		rename(
			USERBASE.'/f/'.$url,
			USERBASE.$newName
		);
		dbQuery(
			'update ads set poster="'.addslashes($newName).'" where id='.$ad_id
		);
	}
	// }
	// { image
	$url=false;
	$dir=new DirectoryIterator(
		USERBASE.'/f/userfiles/'.$data['user_id'].'/ads-upload'
	);
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		$url='userfiles/'.$data['user_id'].'/ads-upload/'.$file->getFilename();
	}
	$newName='/f/userfiles/'.$data['user_id'].'/ad-'.$ad_id.'.'
		.preg_replace('/.*\./', '', $url);
	rename(
		USERBASE.'/f/'.$url,
		USERBASE.$newName
	);
	dbQuery(
		'update ads set image_url="'.addslashes($newName).'" where id='.$ad_id
	);
	// }
	if ($type['type']=='1') { // page
		$page=Page::getInstanceByType('ads');
		$pid=$page->id;
		$page->initValues();
		$pid=(int)$page->vars['ads_fullpage_parent'];
		$meta=json_decode($data['meta'], true);
		$body='<h1>'.htmlspecialchars($meta['name']).'</h1>';
		if (isset($meta['address']) && $meta['address']) {
			$body.='<strong>Address</strong>: '.htmlspecialchars($meta['address']).'<br/>';
		}
		if (isset($meta['landline']) && $meta['landline']) {
			$body.='<strong>Landline</strong>: '.htmlspecialchars($meta['landline']).'<br/>';
		}
		if (isset($meta['mobile']) && $meta['mobile']) {
			$body.='<strong>Mobile</strong>: '.htmlspecialchars($meta['mobile']).'<br/>';
		}
		if (isset($meta['email']) && $meta['email']) {
			$body.='<span class="email"><a href="mailto:'.htmlspecialchars($meta['email']).'">Send Email</a></span> ';
		}
		if (isset($meta['url']) && $meta['url']) {
			$body.='<span class="url"><a target="_blank" href="'.htmlspecialchars($meta['url']).'">'
				.'Visit Website</a></span> ';
		}
		if (isset($meta['twitter']) && $meta['twitter']) {
			$body.='<span class="twitter"><a target="_blank" href="http://twitter.com/'.htmlspecialchars(str_replace('@', '', $meta['twitter'])).'">'
				.htmlspecialchars($meta['twitter']).'</a></span> ';
		}
		if (isset($meta['facebook']) && $meta['facebook']) {
			$body.='<span class="facebook"><a target="_blank" href="'.htmlspecialchars($meta['facebook']).'">Facebook</a></span> ';
		}
		$body.=str_replace("\n", '</p><p>', '<p>'.htmlspecialchars($meta['content']).'</p>');
		if (isset($meta['address']) && $meta['address']) {
			$body.='<iframe frameborder="0" height="320" scrolling="no" src="//maps.google.com/maps?q='.htmlspecialchars($meta['address']).'&amp;num=1&amp;t=m&amp;ie=UTF8&amp;z=14&amp;output=embed" width="480"></iframe>';
		}
		dbQuery(
			'insert into pages set parent='.$pid.', date_publish="0000-00-00"'
			.', body="'.addslashes($body).'"'
			.', date_unpublish=date_add(now(), interval '.$data['days'].' day)'
			.', name="'.addslashes($meta['name']).'"'
			.', alias="'.addslashes($meta['name']).'", type=0'
		);
		Core_cacheClear('pages');
	}
	dbQuery('delete from ads_purchase_orders where id='.$id);
}
