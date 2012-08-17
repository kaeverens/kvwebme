<?php
/**
	* functions for displaying search results
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
  * retrieve list of search results
  *
  * @return string HTML list of search results
  */
function Search_showResults() {
	global $PLUGINS;
	// { variables
	global $PAGEDATA;
	$start=(int)@$_REQUEST['start'];
	$search=@$_REQUEST['search'];
	if (!$search) {
		return '<em id="searchResultsTitle">no search text entered</em>';
	}
	$c='';
	// }
	// { pages
	$q=dbAll(
		'select * from pages where (name like "%'.$search.'%" or body like "%'
		.$search.'%") order by edate desc limit '.$start.',20'
	);
	$n=count($q);
	if ($n>0) {
		foreach ($q as $p) {
			Page::getInstance($p['id'], $p);
		}
		$q=array_slice($q, $start, 20);
		$c.='<h2>Page Search Results</h2><em id="searchResultsTitle">';
		if ($n==1) {
			$c.='1 result found';
		}
		else {
			$c.=$n . ' results found';
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
	// { others
	foreach ($PLUGINS as $plugin) {
		if (@$plugin['search']) {
			$c.=$plugin['search']();
		}
	}
	// }
	if (!$c) {
		return '<em id="searchResultsTitle">'
			.__('no results found', 'core').'</em>';
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
			.'alias="__search",body="",type=5,special=2,ord=5000'
		);
		Core_cacheClear('pages', 'page_by_type_5');
		$p=Page::getInstanceByType(5);
	}
	return $p->id;
}
