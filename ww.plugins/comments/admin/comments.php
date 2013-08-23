<?php
/**
	* Shows all comments for the site with a delete link
	*
	* PHP Version 5
	*
	* @category   CommentsPlugin
	* @package    KVWebme
	* @subpackage CommentsPlugin
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @license    GPL Version 2.0
	* @link       www.kvweb.me
	**/

// { global options
echo '<table>';
// { use moderation
echo '<table><tr><th>Don\'t moderate comments for this site?</th>';
$noModeration = 0;
$sql = 'select value from site_vars where name = "comments_no_moderation"';
if (dbOne($sql, 'value')) {
	$noModeration = dbOne($sql, 'value');
}
echo '<td><script>noModeration = '.$noModeration.'</script>';
echo '<input type="checkbox" id="no_moderation"';
if ($noModeration) {
	echo ' checked = "checked"';
}
echo ' /></td></tr>';
// }
// { moderator email address
echo '<tr><th>Moderator email address</th><td><input id="comments_moderatorEmail"';
if (isset($DBVARS['comments_moderatorEmail'])) {
	echo ' value="'.htmlspecialchars($DBVARS['comments_moderatorEmail']).'"';
}
echo ' /></td><tr>';
// }
// { use captchas
echo '<tr><th>Don\'t use captchas for spam filtering?</th>';
$noCaptchas = 0;
$sql = 'select value from site_vars where name = "comments_no_captchas"';
if (dbOne($sql, 'value')) {
	$noCaptchas = dbOne($sql, 'value');
}
echo '<td><input type="checkbox" id="no_captchas"';
if ($noCaptchas) {
	echo ' checked = "checked"';
}
echo ' /></td></tr>';
// }
echo '</table>';
// }
echo '<strong>Comments</strong>';
$comments = dbAll('select * from comments');
echo '<div style="width:80%">';
echo '<table id="comments-table" style="width:100%"><thead><tr>';
echo '<th>Date</th><th>Name</th><th>Email</th><th>URL</th><th>Comment</th>'
	.'<th>Mod</th><th>Edit</th><th>Delete</th></tr></thead><tbody>';
foreach ($comments as $comment) {
	$id = $comment['id'];
	echo '<tr id="comment-'.$id.'">';
	echo '<td>'.$comment['cdate'].'</td>';
	echo '<td>'.$comment['name'].'</td>';
	echo '<td>'.$comment['email'].'</td>';
	echo '<td>'.$comment['homepage'].'</td>';
	echo '<td>'.$comment['comment'].'</td>';
	echo '<td>';
	echo '<a href="javascript:;" onclick="start_moderation('
		.$id.','.((-1*$comment['isvalid'])+1).')">';
	if ($comment['isvalid']) {
		echo 'Unapprove';
	}
	else {
		echo 'Approve';
	}
	echo '</a></td>';
	echo '<td><a href="javascript:;" '
		.'"onclick="start_edit('.$id.',\''.$comment['comment'].'\');">';
	echo 'edit</a></td>';
	echo '<td><a href="javascript:;" onclick="start_delete('.$id.')">[x]</a>';
	echo '</td>';
	echo '</tr>';
}
echo '</tbody></table>';
echo '</div>';
ww_addScript('/ww.plugins/comments/admin/comments.js');
