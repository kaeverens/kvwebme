$(function() {
	$('#blog-options-wrapper').accordion({
		'autoHeight':false
	});
	Blog_showContents();
});
function Blog_showContents() {
	var $main=$('#blog-main').empty();
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
	window.datatable=$('#blogs-contents-table').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"bJQueryUI": true,
		"sAjaxSource": "/a/p=blog/f=getPostsList/all=1",
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			var $link=$('<a href="#"/>')
				.click(function() {
					$.post('/a/p=blog/f=postGet/id='+aData[0], Blog_editPost);
					return false;
				})
				.html(aData[1]);
			$('td:nth-child(2)', nRow).empty().append($link);
			$('td:nth-child(5)', nRow).addClass('author');
			$('td:nth-child(6)', nRow).html(
				'[<a href="javascript:Blog_deleteEntry('+aData[0]+')"'
				+' title="delete">x</a>]'
			);
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
	});
}
function Blog_deleteEntry(id) {
	if (!confirm('are you sure you want to delete this entry?')) {
		return;
	}
	$.post('/a/p=blog/f=postDelete/blog_id='+id, function() {
		window.datatable.fnDraw(false);
	});
}
