$(function () {
	$('#comment-form').validate({
		rules:{
			name:'required',
			email:{
				required:true,
				email:true
			},
			site:{
				url:true
			},
			comment:{
				required:true
			}
		},
		messages:{
			site:{
				url:'The URL should be in the form http://yoursite.domain'
			}
		}
	});
});
$('div.comment-editable').live('mouseover',function() {
	var id = $(this).attr('id').replace(/comment-wrapper-/,'');
	if (document.getElementById('links-'+id)!=null) {
		return;
	}
	$('div.comments- div.comments-actions').remove();
	var mysqldatetime = $(this).attr('cdate');
	var mysqldate = mysqldatetime.substring(8, 10);
	var mysqlmonth = mysqldatetime.substring(5, 7);
	var mysqlyear = mysqldatetime.substring(0, 4);
	var mysqlhour = mysqldatetime.substring(11, 13);
	var mysqlminute = mysqldatetime.substring(14, 16);
	var mysqlsecond = mysqldatetime.substring(17, 19);
	var now = new Date();
	var commentDate 
		= new Date(
			mysqlyear, mysqlmonth-1, mysqldate, 
			mysqlhour, mysqlminute, mysqlsecond
		);
	var nowInMilliseconds = Date.parse(now);
	commentDate = Date.parse(commentDate);
	var timeSinceCommentWasCreated = nowInMilliseconds-commentDate;
	var fifteenMinutes = 15*60*1000;
	var links = '<div id="links-'+id+'" class="comments-actions">';
	if (timeSinceCommentWasCreated<fifteenMinutes) {
		var comment = $(this).attr('comment');
		links+= '<a '
		links+= 'href="javascript:comments_frontend_start_edit'
		+'('+id+', \''+comment+'\');">edit</a> ';
	}
	links+= '<a href="javascript:comments_frontend_start_delete('+id+');">';
	links+= '[x]</a>';
	links+= '</div>';
	$(links).insertBefore('#comment-info-'+id);
});
$('div.comment-editable').live('mouseout',function(e) {
	var id = e.target.id;
	if(!/^comment-wrapper-[0-9]*$/.test(e.target.id)){
		return;
	}
	$("#links-"+($(this).attr('id')).replace(/comment-wrapper-/,'')).remove();
});
function comments_check_captcha() {
	if (window.comments_noCaptchas) {
		return comments_check_success({status:1});
	}
	var correct = $('#recaptcha_challenge_field').val();
	var response = $('#recaptcha_response_field').val();
	$.post(
		'/ww.plugins/comments/frontend/check-captcha.php',
		{
			"challenge":correct,
			"response":response
		},
		comments_check_success,
		"json"
	);
}
function comments_check_success(data) {
	if (data.status) {
		comments_insert_get_vals();
	}
	else {
		alert('The letters you provided do not match the image');
	}
}
function comments_insert_get_vals() {
	var name = $('#comments-name-input').val();
	var email = $('#comments-email-input').val();
	var site = $('#comments-site-input').val();
	var page = $('#comments-page-id').val();
	var comment = $('#comments-comment-input').val();
	comment = trim(comment);
	$.post(
		'/ww.plugins/comments/frontend/insert.php',
		{
			"name":name,
			"email":email,
			"site":site,
			"page":page,
			"comment":comment
		},
		comments_display_thank_you_message,
		"json"
	);
}
function comments_display_thank_you_message(data) {
	if (!data.status) {
		return alert(data.message);
	}
	$('.no-comments').remove();
	var commentString = '<div id="comment-wrapper-'+data.id+'" '
		+'class="comment-wrapper comment-editable"'
		+' cdate="'+data.mysqldate+'" '
		+'comment="'+htmlspecialchars(data.comment)+'">';
	commentString+= '<div id="comment-info-'+data.id+'">'
	commentString+= 'Posted by '+data.name+' on '+data.humandate;
	commentString+= '<div id="comment-'+data.id+'">'
	commentString+= htmlspecialchars(data.comment);
	commentString+= '</div><br /><br /></div>';
	$(commentString).insertBefore('#comment-form');
	alert('Thank you for commenting');
}
function comments_frontend_start_delete(id) {
	if (confirm('Are you sure you want to delete this comment?')) {
		$.post(
			'/ww.plugins/comments/frontend/delete.php',
			{
				"id":id
			},
			comments_frontend_fadeout_comment,
			"json"
		);
	}
}
function comments_frontend_fadeout_comment(data) {
	if (!data.status) {
		return alert('Could not delete this comment');
	}
	$('#'+data.id).fadeOut('slow', comments_frontend_remove_comment);
}
function comments_frontend_remove_comment(data) {
	$(this).remove()
}
function comments_frontend_start_edit(id, comment) {
	$('#comment-'+id).remove();
	$(
		'<div id="comment-edit"><input type="hidden" id="id" value="'+id+'" />'
		+'<textarea id="comment-text">'+comment+'</textarea><br />'
		+'<input type="button" value="Save" '
		+'onclick="javascript:comments_edit_get_vals();" /></div>'
	)
	.insertAfter('#comment-info-'+id);
}
function comments_edit_get_vals() {
	var id = $('#id').val();
	var comment = $('#comment-text').val();
	comment = trim(comment);
	if (comment==null) {
		return alert('The comment must contain some text');
	}
	$.post(
		'/ww.plugins/comments/frontend/edit-comment.php',
		{
			"id":id,
			"comment":comment
		},
		comments_frontend_update_comment,
		"json"
	);
}
function comments_frontend_update_comment(data) {
	$('<div id="comment-'+data.id+'>'+htmlspecialchars(data.comment)+'</div>')
	.insertBefore('#comment-edit');
	$('#'+data.id).attr('comment', htmlspecialchars(data.comment));
	$('#comment-edit').remove();
}
function trim(myString) {
	return myString.replace(/^\s+|\s+$/g, '');
}
