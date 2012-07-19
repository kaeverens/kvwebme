function Blog_editPost(pdata) {
	$('#pages-submit').attr('disabled', true);
	if (!$.fn.datetimepicker) {
		return $.cachedScript('/j/jquery-ui-timepicker-addon.js').done(function() {
			Blog_editPost(pdata)
		});
	}
	if (!$.fn.tagsInput) {
		$('head').append(
			'<link type="text/css" href="/j/jquery.tagsinput.css" rel="stylesheet"/>'
		);
		return $.cachedScript('/j/jquery.tagsinput.js').done(function() {
			Blog_editPost(pdata)
		});
	}
	if (!window.CKEDITOR) {
		return $.cachedScript('/j/ckeditor-3.6.2/ckeditor.js').done(function() {
			$.cachedScript('/j/ckeditor-3.6.2/adapters/jquery.js').done(function() {
				Blog_editPost(pdata)
			});
		});
	}
	if (!$.fn.saorfm) {
		$('head').append(
			'<link type="text/css" href="/j/jquery.saorfm/jquery.saorfm.css" rel="stylesheet"/>'
		);
		return $.cachedScript('/j/jquery.saorfm/jquery.saorfm.js').done(function() {
			Blog_editPost(pdata)
		});
	}
	var $main=$('#blog-main').empty();
	$main=$('#blog-main,.blog-main-wrapper,.blog-article-wrapper').empty();
	var html='<form action="#"><table>'
		// { title
		+'<tr><th style="width:10%">Title</th>'
		+'<td colspan="4" style="width:70%"><input id="blog-title"  style="width:100%"/></td>'
		+'<td style="width:30%"><button class="save">Save</button></td></tr>'
		// }
		// { post
		+'<tr><th>Post</th><td colspan="5"><textarea id="blog-body"/></td></tr>'
		// }
		// { featured post
		+'<tr><th>Featured Post</th><td colspan="5">'
		+'<input id="blog-featured-post" type="checkbox"/></td></tr>'
		// }
		// { featured image
		+'<tr><th>Featured Image</th><td colspan="5"><input id="blog-excerpt-image"/></td></tr>'
		// }
		// { excerpt
		+'<tr><th>Excerpt</th><td colspan="5"><textarea id="blog-excerpt"'
		+' style="height:40px;width:100%"/></td></tr>'
		// }
		// { tags
		+'<tr><th>Tags</th><td colspan="5"><input id="blog-tags" style="width:100%"/></td></tr>'
		// }
		// { dates
		+'<tr>'
		+'<th>Published</th><td><input class="datetime" id="blog-pdate"/></td>'
		+'<th>Created</th><td><input disabled="disabled" class="datetime" id="blog-cdate"/></td>'
		+'<th>Last Update</th><td><input disabled="disabled" class="datetime" id="blog-udate"/></td></tr>'
		// }
		// { comments
		+'<tr><th>Comments</th><td colspan="4"><select id="blog-allow_comments">'
		+'<option value="1">Allow comments</option>'
		+'<option value="0">Do not allow any comments</option>'
		+'</select></td>'
		+'<th><input id="blog-user_id" type="hidden"/>'
		+'<input id="blog-id" type="hidden"/>'
		+'<input id="blog-status" type="hidden"/>'
		+'<button class="save">Save</button></th></tr>'
		// }
		+'</table></form>';
	$main.append(html);
	$('input,textarea,select', $main).each(function() {
		var $this=$(this);
		var id=$this.attr('id')||'';
		var key=$this.attr('id').replace(/blog-/, '');
		$this.attr('name', 'blog_'+key).val(pdata[key]);
	});
	$('#blog-pdate,#blog-cdate,#blog-udate')
		.datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm'
		});
	if (CKEDITOR.instances['blog-body']) {
		CKEDITOR.remove(CKEDITOR.instances['blog-body']);
	}
	$('#blog-body')
		.ckeditor(CKEditor_config);
	if (pdata.featured=='1') {
		$('#blog-featured-post').attr('checked', true);
	}
	if (pdata.status=='1') {
		$('<button class="unpublish">Unpublish</button>')
			.click(function() {
				$('input#blog-status').val(0);
				$('button.save').click();
				return false;
			})
			.insertAfter('button.save');
	}
	else {
		$('<button class="publish">Publish</button>')
			.click(function() {
				$('input#blog-pdate')
					.datepicker('setDate', new Date, true)
					.datepicker('setTime', new Date, true);
				$('input#blog-status').val(1);
				$('button.save').click();
				return false;
			})
			.insertAfter('button.save');
	}
	$('#blog-excerpt-image').saorfm({
		'rpc':'/ww.incs/saorfm/rpc.php',
		'select':'file',
		'prefix':userdata.isAdmin?'':'/users/'+userdata.id
	});
	$('#blog-tags').tagsInput({
		'height':30,
		'width':'100%',
		'delimiter':'|'
	});
	$('button.save', $main).click(function() {
		$.post(
			'/a/p=blog/f=postEdit',
			$('form', $main).serialize(),
			function(ret) {
				if (window.Blog_showContents) {
					$('#pages-submit').attr('disabled', false);
					return Blog_showContents(ret);
				}
				document.location=document.location.toString().replace(/#.*/, '');
			}
		);
		return false;
	});
	$('.shade').remove();
}
