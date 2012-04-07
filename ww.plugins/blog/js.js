$(function() {
	$('.blog-excerpt-wrapper,.blog-article-wrapper').each(function() {
		var $this=$(this);
		var id=+$this.attr('id').replace('blog-entry-', '');
		// { add comments box
		if ($this.is('.blog-article-wrapper')) {
			var show_comments=0;
			if (blog_comments) {
				var html='<div id="blog-comment-wrapper"><h2>Leave a Comment</h2>'
					+'<table><tr><th>Name</th><td><input class="blog-comment-name"/></td></tr>'
					+'<tr><th>Email</th><td><input type="email" class="blog-comment-email"/></td></tr>'
					+'<tr><th>Website</th><td><input class="blog-comment-url"/></td></tr>'
					+'<tr><td colspan="2"><textarea class="blog-comment" style="width:100%;"></textarea></td></tr>'
					+'<tr><td colspan="2"><button class="blog-comment-submit">Submit Comment</button></td></tr>'
					+'</table>';
				var $comments=$(html).appendTo($this);
				$comments.find('button').click(function() {
					
				});
			}
		}
		// }
		if (!window.userdata) {
			return;
		}
		// { add "edit post" button
		if ((userdata.isAdmin)
			|| (userdata.id && userdata.id==$(this).find('.blog-author').data('uid'))
		) {
			$('<a href="javascript:" class="blog-edit">edit post</a>')
				.click(function() {
					$.post('/a/p=blog/f=postGet/id='+id, Blog_editPost);
				})
				.insertAfter($this.find('.blog-date-published'));
		}
		// }
	});
	if (!window.userdata) {
		return;
	}
	var showNewPost=userdata.isAdmin;
	if (window.blog_groups && userdata.groups && userdata.groups.length) {
		for (var i=userdata.groups.length;i--;) {
			if (blog_groups[userdata.groups[i]]) {
				showNewPost=1;
			}
		}
	}
	if (showNewPost) {
		$('<button class="blog-new-entry">New Entry</button>')
			.click(function() {
				var d=new Date,d=d.getFullYear()+'-'+(d.getMonth()<9?'0':'')
					+(d.getMonth()+1)+'-'+(d.getDate()<10?'0':'')+d.getDate()
					+' '+(d.getHours()<10?'0':'')+d.getHours()
					+':'+(d.getMinutes()<10?'0':'')+d.getMinutes()+':00';
				Blog_editPost({
					'title':'',
					'excerpt':'',
					'excerpt_image':'',
					'body':'',
					'tags':'',
					'user_id':window.user_id,
					'cdate':d,
					'published':0,
					'udate':'0000-00-00 00:00:00',
					'pdate':'0000-00-00 00:00:00'
				});
				return false;
			})
			.prependTo('.blog-main-wrapper');
	}
});
function Blog_editPost(pdata) {
	$('<div class="shade" style="position:fixed;left:0;top:0;right:0;bottom:0;background:#000;opacity:.1;z-index:9999"/>').appendTo(document.body);
	$.getScript('/ww.plugins/blog/funcs/Blog_editPost.js', function() {
		Blog_editPost(pdata);
	});
	return true;
}
