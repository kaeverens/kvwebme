<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if(!isset($_REQUEST['amt']) || !isset($_REQUEST['t_cost']) || !isset($_REQUEST['img']) || !isset($_REQUEST['pid']))die('{error:"missing variables in request"}');

// { check amt
$amt=(int)$_REQUEST['amt'];
if($amt<1)die('{error:"cannot add less than one item to basket"}');
// }
// { check page id
$pid=(int)$_REQUEST['pid'];
$page=Page::getInstance($pid);
if($page->type != 'image-gallery')die('{error:"incorrect pid given"}');
$page->initValues();
$vars=$page->vars;
// }
// { check cost
$v=false;
$cost=(float)$_REQUEST['t_cost'];
$desc=$_REQUEST['t_desc'];
for($i=0;isset($vars['image_gallery_prices_'.$i]);++$i){
	$r_cost=(float)preg_replace('/[^0-9.]/','',$vars['image_gallery_prices_'.$i]);
	if($r_cost==$cost && $desc==$vars['image_gallery_pricedescs_'.$i]){
		$v=array($cost,$desc);
	}
}
if($v===false)die('{error:"no matching costs found"}');
// }
// { get image URL
$img=(int)$_REQUEST['img'];
if($img<1)die('{error:"no item requested"}');
require_once KFM_BASE_PATH.'/api/api.php';
require_once KFM_BASE_PATH.'/initialise.php';
$file=kfm_getFileDetails($img);
if(is_null($file['name']))die('{error:"no such item"}');
$name=preg_replace('/\..*/','',$file['name']);
$url=kfm_getFileUrl($img);
// }
// { add to cart
if(!function_exists('OnlineStore_addToCart'))die('{error:"online store plugin not enabled"}');
$short_desc=$name;
if($desc!='')$short_desc.=' ('.$desc.')';
OnlineStore_addToCart($cost,$amt,'<img src="/kfmget/'.$img.',width=24,height=16" />'.htmlspecialchars($short_desc),$url,md5($url.'|'.$desc),$_SERVER['HTTP_REFERER']);
echo json_encode($_SESSION['online-store']);
// }
