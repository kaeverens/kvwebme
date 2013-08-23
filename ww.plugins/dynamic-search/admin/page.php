<?php
/**
	* Webme Dynamic Search Plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

ww_addScript('/ww.plugins/dynamic-search/files/admin.js');
$q=dbAll('select value from site_vars where name="catags"');
$catags=explode(',', $q[0]['value']);

if ($_POST['dynamic_submit']) {
	$add=addslashes($_POST['dynamic_newcat']);
	$id=dbOne('select id from pages where name="'.$add.'"', 'id');
	if (in_array($add, $catags)) {
		$error='That category already exists.';
	}
	if ($id==''||!$id) {
		$error='The category must be a pagename.';
	}
	elseif (!$error) {
		array_push($catags, $add);
		dbQuery(
			'update site_vars set value="'
			.implode(',', $catags).'" where name="catags"'
		);
		$error='Category Added';
	}
}

$delete=addslashes($_GET['dynamic_delete_cat']);
if ($delete!='') {
	$num=0;
	foreach ($catags as $catag) {
		if ($catag!=$delete) {
			$num++;
			if ($num==1) {
				$newcats=$catag;
			}
			else {
				$newcats.=','.$catag;
			}
		}
	}
	dbQuery('update site_vars set value="'.$newcats.'" where name="catags"');
	$error='Category Deleted';
	$catags=explode(',', $newcats);
}

$html='<div id="dynamic-search-tabs"><ul><li><a href="#dynamic-search-optio'
	.'ns">Search Options</a></li><li><a href="#dynamic-search-popular">Popula'
	.'r Searches</a></li></ul><div id="dynamic-search-options"><form method="'
	.'post"><table style="margin:10px;width:65%"><tr><td colspan="3"><i>A sea'
	.'rch category must be a pagename. The search engine will then search tha'
	.'t page and all subpages</i></td></tr>';
if (isset($error)) {
	$html.='<em>'.$error.'</em>';
}
$html.='<tr><td>New Category:</td><td><input type="text" name="dynamic_newc'
	.'at" style="width:90%"/</td><td><input type="submit" value="Add" name="d'
	.'ynamic_submit"/></tr></table><table style="width:50%"><tr><td></td><th '
	.'style="text-align:center">Categories</th></tr><tr><td>1</td><td>Site Wi'
	.'de</td><td></td></tr>';

$num=1;
$id=$_GET['id'];;
foreach ($catags as $catag) {
	if ($catag!='') {
		$num++;
		$html.='<tr><td>'.$num.'</td><td>'.$catag.'</td><td><a href="?dynamic_d'
			.'elete_cat='.$catag.'">[x]</a></tr>';
	}
}

$html.='</table></form></div><div id="dynamic-search-popular">';

$q=dbAll(
	'select *, count(search) as occurances from latest_search group by search'
	.' order by occurances desc limit 8'
);
$c=count($q);
if ($c==0) {
	$html.='<p><i>No popular searches found...</i></p>';
}
else {
	$html.='<table style="margin:10px"><colgroup><col style="width:10px"/><co'
		.'l style="width:75%"/><col style="width:15%"/><th style="text-align:le'
		.'ft">Count</th><th style="text-align:left">Search</th><th style="text-'
		.'align:left">Category</th>';
	foreach ($q as $r) {
		if ($r['search']!='') {
			$html.='<tr><td>'.$r['occurances'].'</td><td>'.$r['search']
				.'</a></td><td>'.$r['category'].'</td></tr>';
		}
	}
	$html.='</table></div></div>';
}
