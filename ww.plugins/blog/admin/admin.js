$(function() {
	$('#blog-options-wrapper').accordion({
		'autoHeight':false
	});
	function showContents() {
		var $main=$('#blog-main').empty();
		if ($.inArray('comments', webmePlugins)==-1) {
			$main.append('<em>The Comments plugin is not installed.</em>');
		}
		if ($.inArray('rss', webmePlugins)==-1) {
			$main.append('<em>The RSS plugin is not installed.</em>');
		}
		$main.append('<button class="blogs-add-post __" lang-context="blog">'
			+'Add Post</button>');
		$main.append('<table id="blogs-contents-table">'
			+'<thead><tr><th>#</th><th>Post Name</th><th>Comments</th>'
			+'<th>Dates</th>'
			+'<th>Author</th><th>&nbsp;</th></tr></thead>'
			+'<tbody></tbody>'
			+'</table>');
		$main.append('<br style="clear:both"/>');
		$('#blogs-contents-table').dataTable({
			"bProcessing": true,
			"bServerSide": true,
			"bJQueryUI": true,
			"sAjaxSource": "/a/p=blog/f=getPostsList/all=1",
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
				var $link=$('<a href="#"/>')
					.click(function() {
						$.post('/a/p=blog/f=adminPostGet/id='+aData[0], editPost);
						return false;
					})
					.html(aData[1]);
				$('td:nth-child(2)', nRow).empty().append($link);
				$('td:nth-child(5)', nRow).addClass('author');
				return nRow;
			},
			'fnDrawCallback': function() {
				if (!window.usernames) {
					window.usernames=[];
				}
				var $authors=$('.author');
				var i=0;
				function nextAuthor(ret) {
					if (i>$authors.length) {
						return;
					}
					var $author=$($authors[i]);
					var id=+$author.text();
					if (ret) {
						usernames[id]=ret.name;
					}
					if (usernames[id]) {
						$author.text(usernames[id]);
						++i;
						setTimeout(nextAuthor, 1);
					}
					else {
						$.post('/a/p=blog/f=getUserName/id='+id, nextAuthor);
					}
				}
				nextAuthor();
			}
		});
		$('.blogs-add-post').click(function() {
			var d=new Date,d=d.getFullYear()+'-'+(d.getMonth()<9?'0':'')
				+(d.getMonth()+1)+'-'+(d.getDate()<10?'0':'')+d.getDate()
				+' '+(d.getHours()<10?'0':'')+d.getHours()
				+':'+(d.getMinutes()<10?'0':'')+d.getMinutes()+':00';
			editPost({
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
		});
	}
	function editPost(pdata) {
		var $main=$('#blog-main').empty();
		var html='<form action="#"><table>'
			+'<tr><th>Title</th><td><input id="blog-title"/></td>'
			+'<td><button>Save</button></td></tr>'
			+'<tr><th>Post</th><td colspan="2"><textarea id="blog-body"/></td></tr>'
			+'<tr><th>Excerpt</th><td colspan="2"><textarea id="blog-excerpt"'
			+' style="height:80px"/></td></tr>'
			+'<tr><th>Excerpt Image</th><td colspan="2" id="blog-excerpt_image"/></tr>'
			+'<tr><th>Tags</th><td colspan="2"><input id="blog-tags"/></td></tr>'
			+'<tr><th>Published</th><td colspan="2"><input class="datetime" id="blog-pdate"/></td></tr>'
			+'<tr><th>Created</th><td colspan="2"><input class="datetime" id="blog-cdate"/></td></tr>'
			+'<tr><th>updated</th><td colspan="2"><input class="datetime" id="blog-udate"/></td></tr>'
			+'<tr><td/><th colspan="2"><input id="blog-user_id" type="hidden"/>'
			+'<input id="blog-id" type="hidden"/><button>Save</button></th></tr>'
			+'</table></form>';
		$main.append(html);
		$('#blog-main input,#blog-main textarea').each(function() {
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
		$('#blog-main button').click(function() {
			$.post(
				'/a/p=blog/f=postEdit',
				$('#blog-main form').serialize(),
				showContents
			);
			return false;
		});
	}
	showContents();
});
