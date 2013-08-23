<?php
/**
  * Forum functions
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Forum
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

/**
  * get a breadcrumb list of forum links
  *
  * @param object &$PAGEDATA the page object
	* @param int    &$id       the right-most forum's ID
  *
  * @return string HTML of the breadcrumb
  */
function Forum_getForumBreadcrumbs(&$PAGEDATA, &$id) {
	$f=dbRow('select name,parent_id from forums where id='.$id);
	$c='&raquo; <a href="'.$PAGEDATA->getRelativeUrl().'?forum-f='.$id
		.'">'.htmlspecialchars($f['name']).'</a>';
	if ($f['parent_id']) {
		$c=Forum_getForumBreadcrumbs($PAGEDATA, $f['parent_id']).' '.$c;
	}
	return $c;
}

/**
  * display the forums, threads, and posts
  *
  * @param object &$PAGEDATA the page object
  *
  * @return string HTML of the forum
  */
function Forum_show(&$PAGEDATA) {
	$view=0;
	if (isset($_REQUEST['forum-t'])) {
		$view=2;
		$thread_id=(int)$_REQUEST['forum-t'];
	}
	else if (isset($_REQUEST['forum-f'])) {
		$view=1;
		$forum_id=(int)$_REQUEST['forum-f'];
	}
	if ($view==0) {
		$forums=dbAll(
			'select * from forums where parent_id=0 and page_id='.$PAGEDATA->id
		);
		if (!$forums) {
			dbQuery(
				'insert into forums '.
				'values(0,'.$PAGEDATA->id.',0,"default", "1")'
			);
			$view=1;
			$forum_id=dbLastInsertId();
		}
		else {
			if (count($forums)==1) {
				$view=1;
				$forum_id=$forums[0]['id'];
			}
		}
	}
	switch($view){
		case 1: // { specific forum
			$c=Forum_showForum($PAGEDATA, $forum_id);
		break;
		// }
		case 2: // { specific thread
			$c=Forum_showThread($PAGEDATA, $thread_id);
		break;
		// }
		default: // { show all forums
			$c=Forum_showForums($PAGEDATA, $forums);
			// }
	}
	if (!isset($PAGEDATA->vars['footer'])) {
		$PAGEDATA->vars['footer']='';
	}
	return $PAGEDATA->render()
		.$c
		.$PAGEDATA->vars['footer'];
}

/**
  * display a specific forum
  *
  * @param object &$PAGEDATA the page object
	* @param int    &$id       the forum's ID
  *
  * @return string HTML of the forum creation tool
  */
function Forum_showForum(&$PAGEDATA, &$id) {
	WW_addCSS('/ww.plugins/forum/frontend/forum.css');
	// { forum data
	$forum=dbRow('select * from forums where id='.$id);
	if (!$forum || !count($forum)) {
		return '<em class="error">Error: this forum does not exist!</em>';
	}
	$c='<h2 class="forum-name">'.htmlspecialchars($forum['name']).'</h2>';
	// }
	// { subforums
	$subforums=dbAll('select * from forums where parent_id='.$id);
	if ($subforums && count($subforums)) {
		return 'TODO: subforums';
	}
	// }
	// { threads
	$threads=dbAll(
		'select * from forums_threads '
		.'where forum_id='.$id.' order by last_post_date desc'
	);
	if ($threads && count($threads)) {
		$c.='<table id="forum-threads">'
			.'<tr><th>Topics</th><th>Replies</th>'
			.'<th>Author</th><th>Last Post</th></tr>';
		foreach ($threads as $thread) {
			$user=User::getInstance($thread['creator_id']);
			$last_user=User::getInstance($thread['last_post_by']);
			$user_name=$user?$user->get('name'):'';
			$last_user_name=$last_user?$last_user->get('name'):'';
			$c.='<tr><td><a href="'.$PAGEDATA->getRelativeUrl()
				.'?forum-f='.$id.'&amp;forum-t='.$thread['id'].'">'
				.htmlspecialchars($thread['name']).'</td><td>'
				.($thread['num_posts']-1).'</td>'
				.'<td>'.htmlspecialchars($user_name).'</td><td>'
				.Core_dateM2H($thread['last_post_date'], 'datetime').', by '
				.htmlspecialchars($last_user_name).'</td></tr>';
		}
		$c.='</table>';
	}
	else {
		$c.='<div class="forum-no-threads"><p>This forum has no threads in it.'
			.' Be the first to post to it!</p></div>';
	}
	// }
	// { post form
	if (isset($_SESSION['userdata']) && $_SESSION['userdata']['id']) {
		$c.='<div id="forum-post-submission-form"><script defer="defer">var forum_id='
			.$id.';</script></div>';
		WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
		WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
		WW_addScript('forum/frontend/forum.js');
	}
	else {
		$c.='<div class="forum-not-logged-in">In order to post to this forum,'
			.' you must <a href="/_r?type=loginpage">login'
			.'</a> first.</div>';
	}
	// }
	return $c;
}

/**
  * display all forums in the current page
  *
  * @param object &$PAGEDATA the page object
	* @param array  &$forums   database rows
  *
  * @return string HTML of the forum creation tool
  */
function Forum_showForums(&$PAGEDATA, &$forums) {
	$c='<div class="forums-list"><div class="forums-list-intro">'
		.'Forums on this page</div>';
	foreach($forums as $forum) {
		$c.='<div class="forum-forum">'
			.'<a href="'.$PAGEDATA->getRelativeURL.'?forum-f='.$forum['id'].'">'
				.$forum['name'].'</a></div>';
	}
	$c.='</div>';
	return $c;
}

/**
  * display a specific thread
  *
  * @param object &$PAGEDATA the page object
	* @param int    &$id       the thread's ID
  *
  * @return string HTML of the forum creation tool
  */
function Forum_showThread(&$PAGEDATA, &$id) {
	require_once SCRIPTBASE.'ww.incs/bb2html.php';
	WW_addCSS('/ww.plugins/forum/frontend/forum.css');
	$script='$(function(){$(".ratings").ratings();});';
	WW_addScript('/j/jquery.tooltip.min.js');
	WW_addScript('ratings/ratings.js');
	WW_addInlineScript($script);
	$thread=dbRow('select * from forums_threads where id='.$id);
	$forum_id=$thread['forum_id'];
	if (!$thread || !count($thread)) {
		return '<em class="error">Error: this thread does not exist!</em>';
	}
	$c=Forum_getForumBreadcrumbs($PAGEDATA, $thread['forum_id'])
		.' &raquo; <a href="'.$PAGEDATA->getRelativeUrl().'?forum-f='.$forum_id
		.'&forum-t='.$id.'">'.htmlspecialchars($thread['name']).'</a>';
	$c.='<table id="forum-posts"><tr><th>Author</th><th>Post</th></tr>';
	$posts=dbAll(
		'select * from forums_posts where thread_id='
		.$id.'  and moderated = 1 order by created_date'
	);
	foreach ($posts as $post) {
		$user=User::getInstance($post['author_id']);
		if ($user) {
			$user_name=$user->get('name');
			$user_id=$post['author_id'];
			$user_email=$user->get('email');
		}
		else {
			$user_name='unknown';
			$user_id=0;
			$user_email='';
		}
		$c.='<tr p-data=\'({"id":'.$post['id']
			.',"cdate":"'.$post['created_date'].'"'
			.',"uid":'.$post['author_id'].'})\'>'
			.'<td class="user-details"><a name="forum-c-'.$post['id']
			.'"></a>'.htmlspecialchars($user_name).'</td>'
			.'<td><div class="post-header">Posted: '
			.Core_dateM2H($post['created_date'], 'datetime')
			.'</div></td></tr>';
		$count_posts=$user_id
			?dbOne(
				'select count(id) from forums_posts where author_id='.$user->get('id'),
				'count(id)'
			)
			:0;
		$emailHash=md5(trim(strtolower($user_email)));
		
		$c.='<tr><td><img class="avatar" data-uid="'.$user_id.'" />'
			. '<span>Posts: '.$count_posts.'</span>'
			. '<p>Helpfulness:'
			. '<span class="ratings" id="forum_user_'.$user_email.'"'
			. ' type="forum_user">rating</span></p>';				

		$c.='</td><td class="post">'.bb2html($post['body'])
			.'</td></tr>';
	}
	$c.='</table>';
	// { post form
	if (isset($_SESSION['userdata']) && $_SESSION['userdata']['id']) {
		$c.='<div id="forum-post-submission-form"><script defer="defer">var forum_id='
			.$forum_id.',forum_thread_id='.$id.';</script></div>';
		WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
		WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
		WW_addScript('forum/frontend/forum.js');
	}
	else {
		$c.='<div class="forum-not-logged-in">In order to post to this thread,'
			.' you must <a href="/_r?type=loginpage">login'
			.'</a> first.</div>';
	}
	// }
	return $c;
}
