<?php
/**
	* search results
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { getDescendants

/**
	* getDescendants
	*
	* @param int $id ID
	*
	* @return whatever
	*/
function getDescendants($id) {
	$s=' or parent='.$id;
	$q=dbAll('select id from pages where parent="'.$id.'"');
	$n=count($q);
	if ($n==0) {
		return $s;
	}
	foreach ($q as $r) {
		$s.=getDescendants($r['id']);
	}
	return $s;
}

// }
// { DynamicSearch_catags

/**
	* DynamicSearch_catags
	*
	* @param array  $catags categories
	* @param string $s      search string
	* @param string $cat    category to search
	* @param int    $limit  how many results to return
	*
	* @return array
	*/
function DynamicSearch_catags($catags, $s, $cat, $limit) {
	if (!in_array($cat, $catags)) {
		die('Category does not exist.');
	}
	$id=dbOne('select id from pages where name="'.$cat.'"', 'id');
	$gd=getDescendants($id);
	$q=dbAll(
		'select * from pages where (id='.$id.' '.$gd.') and (body like "%'.$s
		.'%" or name like "%'.$s.'%") order by edate limit '.$limit
	);
	return $q;
}

// }

$s=addslashes($_GET['dynamic_search']);
$cat=addslashes($_GET['dynamic_category']);
if ($cat=='') {
	$cat='Site Wide';
}

$p=$_GET['dynamic_page'];
if ($p==0) {
	$p=1;
}
$l=$p*10;
$m=$l-10;
$limit=$m.','.$l;

dbQuery(
	'insert into latest_search values ("","'.$s.'","'.$cat.'","'
	.$_SERVER['REQUEST_TIME'].'","'.date('dd/mm/yy').'")'
);

if ($cat=='Site Wide') {
	$q=dbAll(
		'select * from pages where name like "%'.$s.'%" or body like "%'.$s
		.'%" order by edate limit '.$limit
	);
}
else {
	$q=DynamicSearch_catags($catags, $s, $cat, $limit);
}

$n=count($q);

$c='<div id="dynamic_searches"><div id="dynamic_search_results">';

if ($n==10) {
	$c.='<p class="right" style="margin-top:-20px"><a href="?dynamic_search_s'
		.'ubmit=search&dynamic_search='.$s.'&dynamic_category='.$cat.'&dynamic_'
		.'page='.($p+1).'">Next Page</a></p>';
}
if ($p>1) {
	$c.='<p style="margin-bottom:20px"><a href="?dynamic_search_submit=search'
		.'&dynamic_search='.$s.'&dynamic_category='.$cat.'&dynamic_page='.($p-1)
		.'">Previous Page</a></p>';
}

if ($n==0||!$n) {
	$c.='<i>No search results found for "'.$s.'" in category "'.$cat.'". Plea'
		.'se try less keywords.</i>';
}
else {
	$c.='<ul id="dynamic_list" style="margin-top:40px">';
	$num=($p==0)?0:$m;
	for ($i=0;$i<=($n-1);$i++) {
		$num++;
		$title=($q[$i]['title']=='')?$q[$i]['name']:$q[$i]['title'];
		$c.='<li><h4>'.$num.'. &nbsp;&nbsp;'.str_replace(
			$s,
			'<span class="dynamic_searched">'.$s.'</span>',
			htmlspecialchars($title)
		)
			.'</h4>';
		$content=str_replace(
			$s,
			'<span class="dynamic_searched">'.$s.'</span>',
			substr(preg_replace('/<[^>]*>/', '', $q[$i]['body']), 0, 200)
		);
		$c.='<p>'.$content.'...';
		$c.='<br /><a href="/'.urlencode($q[$i]['name']).'">/'
			.htmlspecialchars($q[$i]['name']).'</a></p></li>';
	}
	$c.='</ul>';
}

if ($n==10) {
	$c.='<p class="right"><a href="?dynamic_search_submit=search&dynamic_sear'
		.'ch='.$s.'&dynamic_category='.$cat.'&dynamic_page='.($p+1).'">Next Pag'
		.'e</a></p>';
}
if ($p>1) {
	$c.='<p class="left"><a href="?dynamic_search_submit=search&dynamic_searc'
		.'h='.$s.'&dynamic_category='.$cat.'&dynamic_page='.($p-1).'">Previous '
		.'Page</a></p>';
}

$html.=$c.'<br style="clear:both"/></div></div>';
