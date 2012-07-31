<?php

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
function Ads_adminAdDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from ads where id='.$id);
	return array(
		'ok'=>1
	);
}
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
function Ads_adminAdGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from ads where id='.$id);
}
function Ads_adminTypesList() {
	$adTypes=array();
	$rs=dbAll('select id,name from ads_types order by name');
	foreach($rs as $r) {
		$adTypes[$r['id']]=$r['name'];
	}
	return $adTypes;
}
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
		dbQuery('insert into '.$sql);
	}
}
