$(function() {
	var showNewPost=userdata.isAdmin, authorIds=[], authors=[];
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
					$.post('/a/p=blog/f=commentAdd', {
						'page_id':pagedata.id,
						'blog_entry_id':id,
						'name':$('.blog-comment-name').val(),
						'email':$('.blog-comment-email').val(),
						'url':$('.blog-comment-url').val()
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						if (ret.message) {
							alert(ret.message);
						}
						document.location=ret.url;
					});
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
					'udate':'0000-00-00 00:00:00',
					'pdate':'0000-00-00 00:00:00',
					'comments':0,
					'allow_comments':1,
					'status':1
				});
				return false;
			})
			.prependTo('.blog-main-wrapper');
	}
	// { author images
	function updateAuthorIcons() {
		$('.blog-author-img').each(function() {
			var $this=$(this);
			var uid=+$this.data('uid');
			if (!authors[uid].avatar) {
				return;
			}
			$this
				.css({
					'width':'auto',
					'height':'auto'
				})
				.attr('src', '/a/f=getImg/w=24/h=24/'+authors[uid].avatar);
		});
	}
	$('.blog-author').each(function() { // get author images
		var $this=$(this);
		var uid=+$this.data('uid');
		if ($.inArray(uid, authorIds)==-1) {
			authorIds.push(uid);
			authors[uid]={
				'avatar':false,
				'name':$this.text()
			};
		}
		$('<span class="blog-author-img-wrapper">'
			+'<img style="width:24px;height:24px;display:inline-block"'
			+' src="/i/silhouette-24x24.png" class="blog-author-img"'
			+' data-uid="'+uid+'"/></span>')
			.prependTo(this)
			.css('cursor', 'pointer')
			.click(function() {
				var uid=+$(this).find('img').data('uid');
				var editThis=userdata.id && uid==userdata.id;
				var author=authors[uid];
				var avatar=author.avatar
					?'/a/f=getImg/w=256/h=256/'+author.avatar
					:'/i/silhouette-256x256.png';
				var avatarSrc=author.avatar
					?author.avatar
					:'';
				var avatarEdit=editThis
					?'<br/><table><tr><th>Image</th>'
					+'<input class="saorfm user-avatar"/></td></tr></table>'
					:'';
				var $dialog=$('<div><h1>'+author.name+'</h1>'
					+'<img src="'+avatar+'" id="dialog-author-img"/>'
					+avatarEdit
					+'</div>').dialog({
						'modal':true
					});
				if (editThis) {
					$dialog.find('.user-avatar')
						.val(avatarSrc)
						.change(function() {
							var $this=$(this);
							var src=$this.val();
							authors[uid].avatar=src;
							$.post('/a/f=userSetAvatar', {
								'src': src
							});
							$dialog.find('#dialog-author-img')
								.replaceWith('<img src="/a/f=getImg/w=256/h=256/'
									+src+'"/>');
							updateAuthorIcons();
						})
						.saorfm({
							'rpc':'/ww.incs/saorfm/rpc.php',
							'select':'file',
							'prefix':userdata.isAdmin?'':'/users/'+userdata.id
						});
				}
			});
	});
	$.get('/a/f=usersAvatarsGet', {
		ids: authorIds
	}, function(ret) {
		for (var i=0;i<ret.length;++i) {
			authors[+ret[i].id].avatar=ret[i].avatar;
		}
		updateAuthorIcons();
	}, 'json');
	// }
});
function Blog_editPost(pdata) {
	$('<div class="shade" style="position:fixed;left:0;top:0;right:0;bottom:0;'
		+'background:#000;opacity:.1;z-index:9999"/>').appendTo(document.body);
	$.getScript('/ww.plugins/blog/funcs/Blog_editPost.js', function() {
		Blog_editPost(pdata);
	});
	return true;
}
