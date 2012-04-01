<?php
/**
  * Blog plugin excerpts
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

$rs=dbAll('select * from blog_entry order by cdate desc limit '.$excerpts_offset.','.$excerpts_per_page);
$c='<div class="blog-main-wrapper">';
$excerpt_length=(int)$PAGEDATA->vars['blog_excerpt_length'];
if (!$excerpt_length) {
	$excerpt_length=200;
}
foreach ($rs as $r) {
	$c.='<div class="blog-excerpt-wrapper">';
	$c.='<h2 class="blog-header">'.htmlspecialchars($r['title']).'</h2>';
	$user=User::getInstance($r['user_id']);
	$name=$user?$user->name:'unknown';
	$c.='<span class="blog-author">'.$name.'</span> ~ '
		.'<span class="blog-date-created">'.date_m2h($r['cdate']).'</span>';
	$excerpt=$r['excerpt']
		?$r['excerpt']
		:substr(preg_replace('/<[^>]*>/', ' ', $r['body']), 0, $excerpt_length).'...';
	$c.='<div class="blog-excerpt">'.$excerpt.'</div>';
	$date=preg_replace('/ .*/', '', $r['cdate']);
	$c.='<a class="blog-link-to-article" href="'
		.$PAGEDATA->getRelativeUrl().'/'.$r['user_id'].'/'.$date.'/'
		.preg_replace('/[^a-zA-Z0-9]/', '-', transcribe($r['title']))
		.'">read more</a>';
	$c.='</div>';
}
$this_page=(int)($excerpts_offset/$excerpts_per_page);
$c.='<a class="blog-link-to-older-entries" href="'
	.$PAGEDATA->getRelativeURL().'/page'.($this_page+1).'">'.'older entries</a>';
if ($this_page) {
	$c.='<a class="blog-link-to-newers-entries" href="'
		.$PAGEDATA->getRelativeURL().'/page'.($this_page-1).'">'
		.'newer entries</a>';
	}
$c.='</div>';
