$(function(){
	if (!window.forum_id) {
		return;
	}
	$('<div id="forum-post">'
		+'<strong>Add a Post</strong>'
		+'<label for="forum-post-title">Title<input id="forum-post-title" /></label>'
		+'<label for="forum-post-body">Post<textarea id="forum-post-body"></textarea></label>'
		+'<input id="forum-post-submit" type="submit" value="Send Post" /></div>'
	)
		.appendTo('#forum-post-submission-form');
	if (window.forum_thread_id) {
		$('#forum-post-title')
			.closest('label')
			.css('display','none');
	}
	$('#forum-post-submit')
		.click(function(){
			var vals={
				"title"     : $('#forum-post-title').val(),
				"body"      : bbcode_editor.getData(),
				"forum_id"  : window.forum_id,
				"thread_id" : window.forum_thread_id
			};
			if (!window.forum_thread_id && !vals.title) {
				return alert('no title entered');
			}
			if (!vals.body.length) {
				return alert('no post entered');
			}
			$.post('/a/p=forum/f=post',vals,function(ret){
				if (ret.errors) {
					return alert(ret.errors.join("\n"));
				}
				document.location=document.location.toString().replace(/(\?|#).*/,'')
					+'?forum-f='+ret.forum_id+'&forum-t='+ret.thread_id
					+'&'+ret.post_id+'#forum-c-'+ret.post_id;
			});
		});
	$('#forum-posts>tbody>tr').each(function(){
		var $this=$(this);
		var pdata=$this.attr('p-data');
		if (!pdata) {
			return;
		}
		pdata=eval(pdata);
		if (pdata.uid==userdata.id) {
			var $a=$('<a href="javascript:;">[x]</a>')
				.css({
					"float":"right",
					"font-size":"smaller"
				})
				.click(function(){
					if (confirm('are you sure you want to delete this post?')) {
						$.post('/a/p=forum/f=delete/id='+pdata.id, function(ret){
							if (ret.error) {
								return alert(ret.error);
							}
							document.location.reload();;
						});
					}
				});
			$this.find('td:last-child').prepend($a);
		}
	});
	var bbcode_editor=CKEDITOR.replace( 'forum-post-body', {
		extraPlugins : 'bbcode',
		removePlugins : 'bidi,button,dialogadvtab,div,filebrowser,flash,format,forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,stylescombo,table,tabletools,templates',
		toolbar : [
				['Source', '-', 'Save','NewPage','-','Undo','Redo'],
				['Find','Replace','-','SelectAll','RemoveFormat'],
				['Link', 'Unlink', 'Image'],
				'/',
				['FontSize', 'Bold', 'Italic','Underline'],
				['NumberedList','BulletedList','-','Blockquote'],
				['TextColor', '-', 'Smiley','SpecialChar', '-', 'Maximize']
			],
		smiley_images : [
				'regular_smile.gif','sad_smile.gif','wink_smile.gif','teeth_smile.gif','tounge_smile.gif',
				'embaressed_smile.gif','omg_smile.gif','whatchutalkingabout_smile.gif','angel_smile.gif','shades_smile.gif',
				'cry_smile.gif','kiss.gif'
			],
		smiley_descriptions : [
				'smiley', 'sad', 'wink', 'laugh', 'cheeky', 'blush', 'surprise',
				'indecision', 'angel', 'cool', 'crying', 'kiss'
			]
	});
});
