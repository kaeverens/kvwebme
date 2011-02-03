<?php
/**
	* functions for displaying search results
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     http://webworks.ie/
	*/

/**
  * retrieve list of search results
  *
  * @return string HTML list of search results
  */
function Search_showResults() {
	// { variables
	global $PAGEDATA;
	$start=getVar('start', 0);
	$search=getVar('search');
	if (!$search) {
		return '<em id="searchResultsTitle">no search text entered</em>';
	}
	$totalfound=0;
	$c='';
	// }
	// { pages
	$q=dbAll(
		'select * from pages where (name like "%'.$search.'%" or body like "%'
		.$search.'%") order by edate desc limit '.$start.',20'
	);
	$n=count($q);
	if ($n>0) {
		$totalfound+=$n;
		foreach ($q as $p) {
			Page::getInstance($p['id'], $p);
		}
		$q=array_slice($q, $start, 20);
		$c.='<h2>'.__('Page Search Results').'</h2><em id="searchResultsTitle">';
		if ($n==1) {
			$c.=__('1 result found');
		}
		else {
			$c.=__('%1 results found', $n);
		}
		$c.='</em> <div>';
		if ($start>0) {
			$c.='[<a href="'.$PAGEDATA->getRelativeURL().'?search='
				.urlencode($search).'&amp;start='.($start-20).'">previous 20</a>] ';
		}
		if ($start+20<$n) {
			$c.='[<a href="'.$PAGEDATA->getRelativeURL().'?search='
				.urlencode($search).'&amp;start='.($start+20).'">next 20</a>] ';
		}
		$c.='<ol start="'.($start+1).'" id="searchResults">';
		foreach ($q as $r) {
			$title=($r['title']=='')?$r['name']:$r['title'];
			$c.='<li><h4>'.htmlspecialchars($title).'</h4>'
				.'<p>'.substr(preg_replace('/<[^>]*>/', '', $r['body']), 0, 200)
				.'...<br /><a href="/'.urlencode($r['name']).'?search='.$search
				.'">/'.htmlspecialchars($r['name']).'</a></p></li>';
		}
		$c.='</ol></div>';
	}
	// }
	if (!$totalfound) {
		$c.='<em id="searchResultsTitle">no results found</em>';
	}
	return $c;
}

/**
  * retrieve the search page, or create one if it doesn't exist
  *
  * @return object search page
  */
function Search_getPage() {
	if (isset($_GET['s'])) {
		$_GET['search']=$_GET['s'];
	}
	$p=Page::getInstanceByType(5);
	if (!$p || !isset($p->id)) {
		dbQuery(
			'insert into pages set cdate=now(),edate=now(),name="__search",'
			.'body="",type=5,special=2,ord=5000'
		);
		$p=Page::getInstanceByType(5);
	}
	return $p;
}
