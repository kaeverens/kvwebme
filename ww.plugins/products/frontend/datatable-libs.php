<?php
/**
	* common functions for getting product data
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require 'show.php';

$PAGEDATA=Page::getInstance($_REQUEST['pid']);
$PAGEDATA->initValues();

$columns=explode(
	',',
	preg_replace('/[^a-z0-9\-_,]/', '_', strtolower($_REQUEST['sColumns']))
);
$sort_col=isset($_REQUEST['iSortCol_0'])
	?(int)$columns[(int)$_REQUEST['iSortCol_0']]
	:0;
$sort_dir=isset($_REQUEST['sSortDir_0'])?$_REQUEST['sSortDir_0']:'';
if ($sort_dir!='des') {
	$sort_dir='asc';
}

$search=isset($_REQUEST['sSearch'])?$_REQUEST['sSearch']:'';
$search_arr=array();
for ($i=0; $i<count($columns); ++$i) {
	if (!isset($_REQUEST['sSearch_'.$i]) || $_REQUEST['sSearch_'.$i]==='') {
		continue;
	}
	$search_arr[$columns[$i]]=$_REQUEST['sSearch_'.$i];
}

switch ($PAGEDATA->vars['products_what_to_show']) {
	case '1': // { by type
		$id=(int)$PAGEDATA->vars['products_type_to_show'];
		$products=Products::getByType(
			$id, $search, $search_arr, $sort_col, $sort_dir
		);
	break; // }
	case '2': // { by category
		$id=(int)$PAGEDATA->vars['products_category_to_show'];
		$products=Products::getByCategory(
			$id, $search, $search_arr, $sort_col, $sort_dir
		);
	break; //}
	default:
		Core_quit();
}

$total_records=count($products->product_ids);
$returned_products=array();
