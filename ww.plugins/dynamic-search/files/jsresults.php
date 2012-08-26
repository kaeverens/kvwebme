<?php
/**
	* Dynamic Search plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { getDescendants

/**
	* getDescendants
	*
	* @param int $id ID
	*
	* @return misc
	*/
function getDescendants($id) {
	$s=' or parent='.$id;
	$q=mysql_query('select id from pages where parent="'.$id.'"');	
	$n=mysql_num_rows($q);
	if ($n==0) {
		return $s;
	}
	while ($r=mysql_fetch_array($q)) {
		$s.=getDescendants($r['id']);
	}
	return $s;
}

// }
// { DynamicSearch_catags

/**
	* DynamicSearch_catags
	*
	* @param misc $catags blah
	* @param misc $s      blah
	* @param misc $cat    blah
	* @param misc $limit  blah
	*
	* @return misc
	*/
function DynamicSearch_catags($catags, $s, $cat, $limit) {
	$cat_array=explode(',', $catags);
	if (!in_array($cat, $cat_array)) {
		die('Category does not exist.');
	}
	$i=mysql_query('select id from pages where name="'.$cat.'"');
	$d=mysql_fetch_array($i);
	$id=$d['id'];
	$gd=getDescendants($id);
	$q=mysql_query(
		'select * from pages where (id='.$id.' '.$gd.') and (body like "%'.$s
		.'%" or name like "%'.$s.'%") order by edate limit '.$limit
	);
	return $q;
}

// }

require '../../../.private/config.php';

$connect=mysql_connect(
	$DBVARS['hostname'],
	$DBVARS['username'],
	$DBVARS['password']
);
mysql_select_db($DBVARS['db_name'], $connect);

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

$q=mysql_query('select value from site_vars where name="catags"');
$r=mysql_fetch_assoc($q);
$catags=$r['value'];

mysql_query(
	'insert into latest_search values ("","'.$s.'","'.$cat.'","'
	.$_SERVER['REQUEST_TIME'].'","'.date('d/m/y').'")'
);

if ($cat=='Site Wide') {
	$q=mysql_query(
		'select * from pages where name like "%'.$s.'%" or body like "%'.$s
		.'%" order by edate limit '.$limit
	);
}
else {
	$q=DynamicSearch_catags($catags, $s, $cat, $limit);
}

$n=mysql_num_rows($q);

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

$c='<ul>';
if ($n==0||!$n) {
	$c='<i>No search results found for "'.$s.'" in category "'.$cat.'". Pleas'
		.'e try less keywords.</i>';
}
else {
	$c.='<ul id="dynamic_list">';
	$num=($p==0)?0:$m;
	while ($r=mysql_fetch_assoc($q)) {
		$num++;
		$title=($r['title']=='')?$r['name']:$r['title'];
		$c.='<li><h4>'.$num.'. &nbsp;&nbsp;'
			.str_replace(
				$s,
				'<span class="dynamic_searched">'.$s.'</span>',
				htmlspecialchars($title)
			)
			.'</h4>';
		$content=str_replace(
			$s,
			'<span class="dynamic_searched">'.$s.'</span>',
			substr(preg_replace('/<[^>]*>/', '', $r['body']), 0, 200)
		);
		$c.='<p>'.$content.'...';
		$c.='<br /><a href="/'.urlencode($r['name']).'">/'
			.htmlspecialchars($r['name']).'</a></p></li>';
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

echo $c;
