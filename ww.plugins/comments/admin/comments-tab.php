<?php

/**
  * Displays the contents of the comments tab
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

$id = $page['id'];
// { are comments allowed on this page?
$commentsAllowed = dbOne(
	'select value from page_vars '
	.'where name = "allow_comments" and page_id = '.$id,
	'value'
);
$html= 'Allow comments on this page? ';
$html.= '<input type="checkbox" name="page_vars[allow_comments]"';
if ($commentsAllowed=='on') {
	$html.= ' checked="checked"';
}
$html.= '/>';
// }
// { hide comments on this page?
$html.= '<br />Hide comments on this page? ';
$html.= '<input type="checkbox" name="page_vars[hide_comments]"';
$hideComments 
	= dbOne(
		'select value from page_vars 
		where name="hide_comments" and page_id = '.$id,
		'value'
	);
if ($hideComments) {
	$html.= ' checked="checked"';
}
$html.= ' />';
// }
// { is the message box shown at the top or bottom of the list of comments?
$html.= '<br />Show comment box at top? ';
$html.= '<input type="checkbox" name="page_vars[comments_show_box_at_top]"';
$v = dbOne(
	'select value from page_vars where name="comments_show_box_at_top"'
	.' and page_id = '.$id,
	'value'
);
if ($v=='on') {
	$html.= ' checked="checked"';
}
$html.= ' />';
// }
// { show list of comments
$html.= '<br /><strong>Comments for this page</strong>';
$html.= '<div style="width:80%">';
$html.= '<table id="comments-table" style="width:100%">';
$html.= '<thead><tr><th>Date</th><th>Name</th><th>Email</th><th>URL</th>';
$html.= '<th>Comment</th><th>Mod</th><th>Edit</th><th>Delete</th>';
$html.= '</tr></thead>';
$html.= '<tbody>';
$comments = dbAll('select * from comments where objectid = '.$id);
foreach ($comments as $comment) {
	$id = $comment['id'];
	$html.= '<tr id="comment-'.$id.'">';
	$html.= '<td>'.$comment['cdate'].'</td>';
	$html.= '<td>'.$comment['name'].'</td>';
	$html.= '<td>'.$comment['email'].'</td>';
	$html.= '<td>'.$comment['homepage'].'</td>';
	$html.= '<td>'.$comment['comment'].'</td>';
	$html.= '<td>';
	$html.= '<a href="javascript:;" 
		onclick="start_moderation('.$id.','.((-1*$comment['isvalid'])+1).');">';
	$html.= $comment['isvalid']?'Unapprove':'Approve';
	$html.= '</a></td>';
	$html.= '<td><a href="javascript:;" 
		onclick="start_edit('.$id.',\''.$comment['comment'].'\')">';
	$html.= 'edit</a></td>';
	$html.= '<td><a href="javascript:;" onclick="start_delete('.$id.');">'
		.'[x]</a></td>';
	$html.='</tr>';
}
$html.= '</tbody></table>';
$html.= '</div>';
echo $html;
// }
$noModeration 
	= (int)dbOne(
		'select value from site_vars where name = "comments_no_moderation"',
		'value'
	);
echo '<script>var noModeration='.($noModeration?1:0).';</script>';
ww_addCss('/ww.plugins/comments/admin/comments-table.css');
ww_addScript('/ww.plugins/comments/admin/comments.js');
