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
  * @link       www.webworks.ie
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
	WW_addScript('/ww.plugins/comments/frontend/comments-frontend.js');
	WW_addCSS('/ww.plugins/comments/frontend/comments.css');
	// { order of display
	$commentboxfirst=isset($page->vars['comments_show_box_at_top'])
		&& $page->vars['comments_show_box_at_top'];
	// }
	// { get list of existing comments
	$hideComments=isset($page->vars['hide_comments'])
		&& $page->vars['hide_comments'];
	if ($hideComments) {
		if (isset($_SESSION['comment_ids']) && count($_SESSION['comment_ids'])) {
			$query = 'select * from comments where objectid = '.$page->id;
			$query.= ' and id in (';
			foreach ($_SESSION['comment_ids'] as $comment) {
				$query.= (int)$comment.', ';
			}
			if (is_numeric(strpos($query, ', '))) {
				$query = substr_replace($query, '', strrpos($query, ', '));
				$query.= ')';
			}
			else {
				$query = '';
			}
		}
		else {
			$query = '';
		}
	}
	else {
		$query = 'select * from comments where objectid = '.$page->id;
		$query.= ' and (isvalid = 1 or id in (';
		if (isset($_SESSION['comment_ids']) && is_array($_SESSION['comment_ids'])) {
			foreach ($_SESSION['comment_ids'] as $comment) {
				$query.= (int)$comment.', ';
			}
		}
		if (is_numeric(strpos($query, ', '))) {
			$query = substr_replace($query, '', strrpos($query, ', '));
			$query.= '))';
		}
		else {
			$query = 'select * from comments where objectid = '.$page->id;
			$query.= ' and isvalid = 1';
		}
	}
	if (!empty($query)) {
		$comments = dbAll($query.' order by cdate '.($commentboxfirst?'desc':'asc'));
	}
	$clist='';
	if (count($comments)) {
		$clist = '<div id="start-comments" class="comments-list"><a name="comments"></a>'
			.'<strong>Comments</strong>';
		foreach ($comments as $comment) {
			$id = $comment['id'];
			$datetime = $comment['cdate'];
			$allowedToEdit=is_admin() || (
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
			$clist.= ' on '.date_m2h($datetime).'</div>'
				.'<div id="comment-'.$id.'" class="comments-comment">'.htmlspecialchars($comment['comment'])
				.'</div></div>';
		}
		$clist.='</div>';
	}
	else {
		$clist.= '';
	}
	// { get comment box HTML
	$allowComments = dbOne(
		'select value from page_vars 
		where name = "allow_comments" and page_id = '.$page->id,
		'value'
	);
	$cbhtml=$allowComments=='on'?Comments_showCommentForm($page->id):'';
	// }
	return '<script src="http://ajax.microsoft.com/ajax/jquery.validate/1.5.5/'
		.'jquery.validate.min.js"></script>'
		.($commentboxfirst?$cbhtml.$clist:$clist.$cbhtml);
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
	if (is_logged_in()) {
		$userID = get_userid();
		$user 
			= dbRow(
				'select name, email from user_accounts 
				where id = '.$userID
			);
	}
	$noCaptchas=(int)dbOne('select value from site_vars where name = "comments_no_captchas"', 'value');
	$display= '<form id="comment-form" class="comments-form" method="post" 
		action="javascript:comments_check_captcha();">';
	$display.= '<strong>Add Comment</strong>';
	$display.= '<input type="hidden" name="page" id="comments-page-id" 
		value="'.$pageID.'" />';
	$display.='<table class="comments-form-table"><tr class="comments-name"><th>Name</th>';
	$display.= '<td><input id="comments-name-input" name="name" ';
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
		.'<tr class="comments-comment"><th>Comment</th>'
		.'<td><textarea id="comments-comment-input" name="comment"></textarea></td></tr>';
	if (!$noCaptchas) {
		$display.='<tr><td colspan="2"><div id="captcha" class="comments_captcha">'
			.Recaptcha_getHTML()
			.'</div></td></tr>';
	}
	$display.='<tr class="comments-submit-comment"><th>&nbsp;</th><td><input type="submit" id="submit" '
		.'value="Submit Comment"  /></td></tr>'
		.'</table></form><script>comments_noCaptchas='.$noCaptchas.';</script>';
	return $display;
}
