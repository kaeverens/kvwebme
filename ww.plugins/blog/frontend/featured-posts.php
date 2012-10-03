<?php
/**
	* featured posts
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$howmany=(int)$PAGEDATA->vars['blog_featured_posts'];
$tmpconstraints=$constraints
	?$constraints.' and'
	:' where';
$rs=dbAll(
	'select * from blog_entry'.$tmpconstraints.' featured'
	.' order by pdate desc limit 0,'.$howmany
);
if (!count($rs)) {
	return;
}
$c.='<div id="blog-featured-excerpts"><div class="main">';
$shown=0;
foreach ($rs as $r) {
	$c.='<div class="featured-excerpt"';
	if ($shown++) {
		$c.=' style="display:none"';
	}
	$c.='>';
	// { image
	if (!$r['excerpt_image']) {
		$img=preg_replace(
			'/.*<img.*?src="([^"]*)".*/m',
			'\1',
			str_replace(array("\n", "\r"), ' ', $r['body'])
		);
		if (strpos($img, '/f')===0) {
			$r['excerpt_image']=preg_replace('#^/f/#', '', $img);
		}
	}
	$img='';
	if ($r['excerpt_image']) {
		$img='<img class="blog-excerpt-image" src="/a/f=getImg/w=320/h=200/'
			.$r['excerpt_image'].'"/>';
	}
	// }
	$c.=$img;
	$excerpt=preg_replace('/<[^>]*>/', ' ', $r['body']);
	$date=preg_replace('/ .*/', '', $r['cdate']);
	$c.='<div class="text"><div class="overlay"></div>'
		.'<h2 class="blog-header">'.htmlspecialchars($r['title']).'</h2>'
		.'<div class="blog-excerpt">'.$excerpt.'</div>'
		.'<a class="blog-link-to-article" href="'
		.$PAGEDATA->getRelativeUrl().'/'.$r['user_id'].'/'.$date.'/'
		.preg_replace('/[^a-zA-Z0-9]/', '-', transcribe($r['title']))
		.'">read more</a>'
		.'</div>';
	$c.='</div>';
}
$c.='</div>'
	.'<div class="carousel"></div>'
	.'</div>';
WW_addScript('blog/j/featured.js');
WW_addCSS('/ww.plugins/blog/c/featured.css');
