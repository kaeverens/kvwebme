<?php

/**
	* Displays validated comments
	*
	* PHP Version 5
	*
	* @category   CommentsPlugin
	* @package    WebworksWebme
	* @subpackage CommentsPlugin
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @author     Kae Verens <kae@kvsites.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	**/

require_once SCRIPTBASE.'ww.incs/recaptcha.php';

/**
	* The main display function
	*
	* @param Object $page Page Info
	*
	* @return $html The comments and an add comment form
	**/
function Comments_displayComments($page) {
	if (!$GLOBALS['access_allowed']) {
		return '';
	}
	// { order of display
	$commentboxfirst=isset($page->vars['comments_show_box_at_top'])
		&& $page->vars['comments_show_box_at_top'];
	// }
	// { get list of existing comments
	$hideComments=isset($page->vars['hide_comments'])
		&& $page->vars['hide_comments'];
	if ($hideComments) {
		if (count(@$_SESSION['comment_ids'])) {
			$query='select * from comments where objectid='.$page->id.' and id in ('
				.join(', ', $_SESSION['comment_ids']).')';
		}
		else {
			$query = '';
		}
	}
	else {
		if (count(@$_SESSION['comment_ids'])) {
			$query='select * from comments where objectid='.$page->id
				.' and (isvalid=1 or id in ('.join(', ', $_SESSION['comment_ids']).'))';
		}
		else {
			$query = 'select * from comments where objectid='.$page->id
				.' and isvalid=1';
		}
	}
	if ($query) {
		$sql=$query.' order by cdate '.($commentboxfirst?'desc':'asc');
		$md5=md5($sql);
		$comments=Core_cacheLoad('comments', $md5);
		if ($comments===false) {
			$comments=dbAll($sql);
			Core_cacheSave('comments', $md5, $comments);
		}
	}
	// }
	$clist='';
	if (count($comments)) {
		$clist = '<div id="start-comments" class="comments-list"><a name="comments"></a>'
			.'<strong>Comments</strong>';
		foreach ($comments as $comment) {
			$id = $comment['id'];
			$datetime = $comment['cdate'];
			$allowedToEdit=Core_isAdmin() || (
				(isset($_SESSION['comment_ids'])&&is_array($_SESSION['comment_ids']))
				&& in_array($id, $_SESSION['comment_ids'], false)
			);
			$clist.= '<div class="comment-wrapper';
			if ($allowedToEdit) {
				$clist.= ' comment-editable" '
					.'cdate="'.$datetime.'" comment="'
					.htmlspecialchars($comment['comment']).'"';
			}
			else {
				$clist.= '" ';
			}
			$clist.='id="comment-wrapper-'.$comment['id'].'"'
					.'><a name="comments-'.$id.'"></a>'
					.'<div class="comment-info" id="comment-info-'.$id.'">Posted by ';
			if (!empty($comment['site'])) {

				$clist.= '<a href="'.$comment['site'].'" target=_blank>'
					.htmlspecialchars($comment['name']).'</a>';
			}
			else {
				$clist.= htmlspecialchars($comment['name']);
			}
			$clist.= ' on '.Core_dateM2H($datetime).'</div>'
				.'<div id="comment-'.$id.'" class="comments-comment">'
				.htmlspecialchars($comment['comment'])
				.'</div></div>';
		}
		$clist.='</div>';
	}
	else {
		$clist.= '';
	}
	// { get comment box HTML
	$allowComments=Core_cacheLoad('comments', 'allow-'.$page->id, -1);
	if ($allowComments===-1) {
		$allowComments=dbOne(
			'select value from page_vars where name="allow_comments" and page_id='
			.$page->id,
			'value'
		);
		Core_cacheSave('comments', 'allow-'.$page->id, $allowComments);
	}
	$cbhtml=$allowComments=='on'?Comments_showCommentForm($page->id):'';
	if ($allowComments=='on') {
		WW_addScript('comments/frontend/comments-frontend.js');
		$cbhtml.='<script src="//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/'
			.'jquery.validate.min.js"></script>';
	}
	WW_addCSS('/ww.plugins/comments/frontend/comments.css');
	// }
	return $commentboxfirst?$cbhtml.$clist:$clist.$cbhtml;
}

/**
  * Shows the add comment form
  *
  * @param int $pageID The page that the comment is to be displayed on
  *
  * @return $display The form
  *
**/
function Comments_showCommentForm($pageID) {
	if (isset($_SESSION['userdata'])) {
		$userID =$_SESSION['userdata']['id'];
		$user=dbRow('select name, email from user_accounts where id = '.$userID);
	}
	$noCaptchas=(int)dbOne(
		'select value from site_vars where name = "comments_no_captchas"',
		'value'
	);
	$display= '<form id="comment-form" class="comments-form" method="post" 
		action="javascript:comments_check_captcha();">';
	$display.= '<strong>Add Comment</strong>';
	$display.= '<input type="hidden" name="page" id="comments-page-id" 
		value="'.$pageID.'" />';
	$display.='<table class="comments-form-table"><tr class="comments-name">'
		.'<th>Name</th><td><input id="comments-name-input" name="name" ';
	if (isset($user)) {
		$display.= ' value="'.htmlspecialchars($user['name']).'"';
	}
	$display.= ' /></td></tr>';
	$display.= '<tr class="comments-email"><th>Email</th>';
	$display.= '<td><input id="comments-email-input" name="email"';
	if (isset($user)) {
		$display.= ' value="'.htmlspecialchars($user['email']).'"';
	}
	$display.= ' /></td></tr>'
		.'<tr class="comments-url"><th>Website</th>'
		.'<td><input id="site" name="comments-site-input" /></td></tr>'
		.'<tr class="comments-comment"><th>Comment</th><td>'
		.'<textarea id="comments-comment-input" name="comment"></textarea></td>'
		.'</tr>';
	if (!$noCaptchas) {
		$display.='<tr><td colspan="2"><div id="captcha" class="comments_captcha">'
			.Recaptcha_getHTML()
			.'</div></td></tr>';
	}
	$display.='<tr class="comments-submit-comment"><th>&nbsp;</th><td>'
		.'<input type="submit" id="submit" value="Submit Comment"  /></td></tr>'
		.'</table></form><script defer="defer">comments_noCaptchas='.$noCaptchas.';</script>';
	return $display;
}
