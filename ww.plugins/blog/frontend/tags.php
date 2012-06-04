<?php
/**
  * Blog plugin authors
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

$c='<h1>List of Tags</h1>';

$rs=dbAll(
	'select count(tag) as cnt, tag from blog_tags group by tag order by tag'
);
$tags=array();
foreach ($rs as $r) {
	$h=htmlspecialchars($r['tag']);
	$tags[]='<a href="'.$PAGEDATA->getRelativeUrl().'/tags/'.$h
		.'">'.$h.' <span>('.$r['cnt'].')</span></a>';
}

$c.='<div class="blog-tags">'.join(', ', $tags).'</div>';
