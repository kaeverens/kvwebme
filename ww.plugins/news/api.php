<?php
/**
  * News api
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

function News_getHeadlinesDay() {
	$y=(int)$_REQUEST['y'];
	$m=(int)$_REQUEST['m'];
	$d=(int)$_REQUEST['d'];
	$p=(int)$_REQUEST['pid'];
	if( $y<1000 || $y>9999 || $m<1 || $m>12 || $d<1 || $d>31) {
		Core_quit();
	}
	$m=sprintf('%02d', $m);
	
	$sql='select id from pages where parent='.$p.' and associated_date>"'
		.$y.'-'.$m.'-'.$d.'" and associated_date<date_add("'.$y.'-'.$m.'-'.$d
		.'", interval 1 day) order by associated_date';
	$ps=dbAll($sql);
	$headlines=array();
	foreach ($ps as $p) {
		$page=Page::getInstance($p['id']);
		$headlines[]=array(
			'url'=>$page->getRelativeURL(),
			'adate'=>$page->associated_date,
			'headline'=>htmlspecialchars($page->alias)
		);
	}
	return $headlines;
}
function News_getHeadlinesMonth() {
	$y=(int)$_REQUEST['y'];
	$m=(int)$_REQUEST['m'];
	$p=(int)$_REQUEST['pid'];
	if ($y<1000 || $y>9999 || $m<1 || $m>12) {
		Core_quit();
	}
	$m=sprintf('%02d', $m);
	
	$sql='select id from pages where parent='.$p.' and associated_date>"'.$y.'-'
		.$m.'-00" and associated_date<date_add("'.$y.'-'.$m
		.'-01", interval 1 month) order by associated_date';
	$ps=dbAll($sql);
	$headlines=array();
	foreach ($ps as $p) {
		$page=Page::getInstance($p['id']);
		$headlines[]=array(
			'url'=>$page->getRelativeURL(),
			'adate'=>$page->associated_date,
			'headline'=>htmlspecialchars($page->alias)
		);
	}
	return $headlines;
}
