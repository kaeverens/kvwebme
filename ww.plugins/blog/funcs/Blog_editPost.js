function Blog_editPost(pdata) {
	if (!$.fn.datetimepicker) {
		return $.cachedScript('/j/jquery-ui-timepicker-addon.js').done(function() {
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
	var $main=$('#blog-main').empty();
	$main=$('#blog-main,.blog-main-wrapper,.blog-article-wrapper').empty();
	var html='<form action="#"><table>'
		+'<tr><th>Title</th><td><input id="blog-title"/></td>'
		+'<td><button class="save">Save</button></td></tr>'
		+'<tr><th>Post</th><td colspan="2"><textarea id="blog-body"/></td></tr>'
		+'<tr><th>Excerpt</th><td colspan="2"><textarea id="blog-excerpt"'
		+' style="height:80px"/></td></tr>'
		+'<tr><th>Excerpt Image</th><td colspan="2" id="blog-excerpt_image"/></tr>'
		+'<tr><th>Tags</th><td colspan="2"><input id="blog-tags"/></td></tr>'
		+'<tr><th>Published</th><td colspan="2"><input class="datetime" id="blog-pdate"/></td></tr>'
		+'<tr><th>Created</th><td colspan="2"><input disabled="disabled" class="datetime" id="blog-cdate"/></td></tr>'
		+'<tr><th>Last Update</th><td colspan="2"><input disabled="disabled" class="datetime" id="blog-udate"/></td></tr>'
		+'<tr><td/><th colspan="2"><input id="blog-user_id" type="hidden"/>'
		+'<input id="blog-id" type="hidden"/>'
		+'<input id="blog-status" type="hidden"/>'
		+'<button class="save">Save</button></th></tr>'
		+'</table></form>';
	$main.append(html);
	$('input,textarea', $main).each(function() {
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
	if (pdata.status=='0') {
		$('<button class="publish">Save and Publish</button>')
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
	else {
		$('<button class="unpublish">Save and Unpublish</button>')
			.click(function() {
				$('input#blog-status').val(0);
				$('button.save').click();
				return false;
			})
			.insertAfter('button.save');
	}
	$('button.save', $main).click(function() {
		$.post(
			'/a/p=blog/f=postEdit',
			$('form', $main).serialize(),
			function(ret) {
				if(window.Blog_showContents) {
					return Blog_showContents(ret);
				}
				document.location=document.location.toString().replace(/#.*/, '');
			}
		);
		return false;
	});
	$('.shade').remove();
}
