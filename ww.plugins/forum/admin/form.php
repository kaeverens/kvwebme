<?php
/**
  * admin page for managing forums
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

WW_addScript('forum/admin/form.js');
$c='<link rel="stylesheet" type="text/css" '
	.'href="/ww.plugins/forum/admin/forum-admin.css"></link>';
// { tabs nav
$c.= '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#t-dashboard">Dashboard</a></li>'
	.'<li><a href="#t-forums">Forums</a></li>'
	.'<li><a href="#t-header">Header</a></li>'
	.'<li><a href="#t-footer">Footer</a></li>'
	.'</ul>';
// }
// { dashboard
$c.='<div id="t-dashboard">';
$sql='select users.id as uid,users.name as user, threads.name as thread,'
	.'posts.body as body, posts.id as id, posts.created_date as date,'
	.'forums.name as forum, forums.moderator_groups as groups'
	.' from user_accounts as users, forums_threads as threads, forums_posts '
	.'as posts, forums '
	.'where users.id=posts.author_id and threads.id=posts.thread_id '
	.'and threads.forum_id = forums.id and posts.moderated=0 '
	.'and forums.page_id = '.$page['id'];
$posts = dbAll($sql);
$c.='<strong>Posts Requiring Moderation</strong>';
$c.='<table id="forum-datatable-requires-moderation" class="forum-page-table">'
	.'<thead>';
$c.='<tr><th>Date</th>';
$c.='<th>Author</th>';
$c.='<th>Forum</th>';
$c.='<th>Thread</th>';
$c.='<th>Posts</th>';
$c.='<th>Moderation</th>';
$c.='</tr></thead><tbody>';
foreach ($posts as $post) {
	$c.='<tr id="post-for-moderation-'.$post['id'].'">';
	$c.='<td>'.Core_dateM2H($post['date']).'</td>';
	$c.='<td>'.htmlspecialchars($post['user']).'</td>';
	$c.='<td>'.htmlspecialchars($post['forum']).'</td>';
	$c.='<td>'.htmlspecialchars($post['thread']).'</td>';
	$c.='<td>'.htmlspecialchars($post['body']).'</td>';
	$c.='<td>';
	$c.='<a class="approve" id="approve_'.$post['id'].'" '
		.'href="javascript:;">Approve</a><br />'
		.'<a class="delete" id="delete_'.$post['id'].'" '
		.'href="javascript:;">Delete</a>';
	$c.= '</td></tr>';
}
$c.='</tbody></table></div>';
// }
// { forums
$c.= '<div id="t-forums">';
$forums=dbAll('select name, id from forums where page_id = '.$page['id']);
$groups=dbAll('select name, id from groups');
$c.='<table id="forum-moderators-table">';
$c.='<thead><tr><th>Forum</th>';
$c.='<th>Moderators</th>';
$c.= '<th>&nbsp;</th></tr></thead><tbody>';
if (!count($forums)) {
	require_once dirname(__FILE__).'/../api-admin.php';
	$_REQUEST=array(
		'name'=>'Main Forum',
		'page'=>$page['id']
	);
	Forum_adminForumAdd();
	$forums=dbAll('select name, id from forums where page_id = '.$page['id']);
}
foreach ($forums as $forum) {
	$c.= '<tr id="forum-'.$forum['id'].'"><td>'.htmlspecialchars($forum['name'])
		.'</td><td>';
	foreach ($groups as $group) {
		$c.=htmlspecialchars($group['name'])
			.' <input type="checkbox" name="moderators-'.$forum['id'].'[]"'
			.' value='.$group['id'].' class="moderators"';
		$sql='select moderator_groups from forums where id = '.$forum['id'];
		$mods = explode(',', dbOne($sql, 'moderator_groups'));
		if (in_array($group['id'], $mods, false)) {
			$c.=' checked="checked" ';
		}
		$c.=' /><br />';
	}
	$c.= '<a href="javascript:;" class="add-group" '
		.'id="add-group-link-for-forum-'.$forum['id'].'">[+]</a>';
	$c.='</td><td>'
		.'<a href="javascript:;" id="delete-forum-'.$forum['id'].'" '
		.'class="delete-forum-link">[x]</a></td></tr>';
}
$c.= '</tbody></table>';
$c.='<a href="javascript:;" class="add-forum" page="'.$page['id'].'">[+]</a>'
	.'</div>';
// }
// { header
$c.='<div id="t-header"><p>Text to be shown above the form</p>'
	.ckeditor('body', $page['body'])
	.'</div>';
// }
// { footer
$c.='<div id="t-footer"><p>Text to appear below the form.</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:''),
	0
);
$c.='</div>';
// }
$c.='</div>';
