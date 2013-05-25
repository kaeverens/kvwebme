<?php
/**
  * Blog plugin main contents
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

$bits=explode('/', $unused_uri);
$user_id=(int)$bits[0];
$date=$bits[1];
$titlelike=str_replace('-', '_', $bits[2]);
$sql='select * from blog_entry where user_id='.$user_id
	.' and cast(cdate as date)'
	.'="'.addslashes($date).'" and title like "'.addslashes($titlelike).'"';
$r=dbRow($sql);
if (!$r) {
	$c='<div class="blog-article-error">Error: article not found.</div>';
	return;
}
$c='<div class="blog-article-wrapper" id="blog-entry-'.$r['id'].'">';
$c.='<h1 class="blog-header">'.htmlspecialchars($r['title']).'</h1>';
$user=User::getInstance($r['user_id']);
$name=$user?$user->name:'unknown';
$c.='<div class="blog-meta">'
	.'<span class="blog-author" data-uid="'.$r['user_id'].'">'.$name.'</span>'
	.'<span class="blog-spacing"> ~ </span>'
	.'<span class="blog-date-published">'.Core_dateM2H($r['cdate']).'</span>'
	.'</div>';
$c.='<div class="blog-body">'.$r['body'].'</div>';
$date=preg_replace('/ .*/', '', $r['cdate']);
$c.='</div>';
WW_addScript('blog');
WW_addInlineScript('window.blog_comments=0;');
if ($r['allow_comments']) {
	if (isset($PAGEDATA->vars['blog_fbappid'])
		&& (int)$PAGEDATA->vars['blog_fbappid']
	) {
		$fbappid=(int)$PAGEDATA->vars['blog_fbappid'];
		$c.='<div id="fb-root"></div><script>(function(d, s, id) {'
			.'var js, fjs = d.getElementsByTagName(s)[0];'
			.'if (d.getElementById(id)) return;js = d.createElement(s);'
			.'js.id = id;js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1&appId='
			.$fbappid
			.'";fjs.parentNode.insertBefore(js, fjs);}'
			.'(document, "script", "facebook-jssdk"));</script>';
		$url=$PAGEDATA->getAbsoluteUrl().'/'.$r['user_id'].'/'.$date.'/'
			.preg_replace('/[^a-zA-Z0-9]/', '-', transcribe($r['title']));
		$c.='<div class="fb-comments" data-href="'
			.$url.'" data-num-posts="2"></div>';
		$md5=md5($url);
		$comments=Core_cacheLoad('blog-comments', $md5, -1);
		if ($comments===-1) {
			$comments=file_get_contents(
				'https://graph.facebook.com/comments/?ids='.urlencode($url)
			);
			Core_cacheSave('blog-comments', $md5, $comments);
		}
		$c.='<div style="display:none;">'.$comments.'</div>';
	}
	else {
		$c.='<em>no Facebook App ID set for comments</em>';
	}
}
