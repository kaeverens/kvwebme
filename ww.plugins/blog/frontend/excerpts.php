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

$c=''; // html to return

// { set up constraints

$constraints=array(1);
if (!Core_isAdmin()) {
	if (isset($_SESSION['userdata']) && $_SESSION['userdata']['id']) {
		$constraints[]='(status>0 or user_id='.$_SESSION['userdata']['id'].')';
	}
	else {
		$constraints[]='status';
	}
}
if ($blog_author) {
	$constraints[]='user_id='.$blog_author;
}
if (isset($entry_ids) && $entry_ids) {
	$constraints[]='id in ('.join(',', $entry_ids).')';
}
$constraints=' where '.join(' and ', $constraints);

// }
// { set up Featured Posts slider if there is one
if (isset($PAGEDATA->vars['blog_featured_posts'])
	&& (int)$PAGEDATA->vars['blog_featured_posts']
) {
	require dirname(__FILE__).'/featured-posts.php';
}
// }

$num_of_entries=dbOne(
	'select count(id) ids from blog_entry'.$constraints, 'ids'
);
$sql='select * from blog_entry'.$constraints.' order by cdate desc'
	.' limit '.$excerpts_offset.','.$excerpts_per_page;
$rs=dbAll($sql);
$c.='<div class="blog-main-wrapper">';
$excerpt_length=(int)$PAGEDATA->vars['blog_excerpt_length'];
if (!$excerpt_length) {
	$excerpt_length=200;
}
foreach ($rs as $r) {
	$sclass=$r['status']=='1'?'blog-published':'blog-unpublished';
	$c.='<div class="blog-excerpt-wrapper '.$sclass.'" id="blog-entry-'.$r['id'].'">';
	$c.='<h2 class="blog-header">'.htmlspecialchars($r['title']).'</h2>';
	$user=User::getInstance($r['user_id']);
	$name=$user?$user->name:'unknown';
	$c.='<span class="blog-author" data-uid="'.$r['user_id'].'">'.$name.'</span> ~ '
		.'<span class="blog-date-published">'.Core_dateM2H($r['pdate']).'</span>';
	$excerpt=$r['excerpt']
		?$r['excerpt']
		:substr(preg_replace('/<[^>]*>/', ' ', $r['body']), 0, $excerpt_length).'...';
	// { image
	if (!$r['excerpt_image']) {
		$img=preg_replace('/.*<img.*?src="([^"]*)".*/m', '\1', str_replace(array("\n", "\r"), ' ', $r['body']));
		if (strpos($img, '/f')===0) {
			$r['excerpt_image']=preg_replace('#^/f/#', '', $img);
		}
	}
	$img='';
	if ($r['excerpt_image']) {
		$img='<img class="blog-excerpt-image" src="/a/f=getImg/w=100/h=100/'.$r['excerpt_image'].'"/>';
	}
	// }
	$c.='<div class="blog-excerpt">'.$img.$excerpt.'</div>';
	$date=preg_replace('/ .*/', '', $r['cdate']);
	$c.='<a class="blog-link-to-article" href="'
		.$PAGEDATA->getRelativeUrl().'/'.$r['user_id'].'/'.$date.'/'
		.preg_replace('/[^a-zA-Z0-9]/', '-', transcribe($r['title']))
		.'">read more</a>';
	$c.='</div>';
}
$this_page=(int)($excerpts_offset/$excerpts_per_page);
$bottom_links=array();
if ($num_of_entries>$excerpts_offset+$excerpts_per_page) {
	$bottom_links[]='<a class="blog-link-to-older-entries" href="'
		.$PAGEDATA->getRelativeURL().'/page'.($this_page+1).'">'
		.'older entries</a>';
}
if ($this_page) {
	$bottom_links[]='<a class="blog-link-to-newers-entries" href="'
		.$PAGEDATA->getRelativeURL().'/page'.($this_page-1).'">'
		.'newer entries</a>';
}
$bottom_links[]='<a class="blog-link-to-all-authors" href="'
	.$PAGEDATA->getRelativeURL().'/authors">'
	.'list of authors</a>';
$c.='<div class="blog-bottom-links">'.join(' | ', $bottom_links).'</div>';
$c.='</div>';
