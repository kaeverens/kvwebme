function forums_admin_update_forums_table(data) {
	if (!data.status) {
		return alert(data.message);
	}
	var html = '<tr id="forum-'+data.id+'"><td>'+data.name+'</td><td>';
	var groups = data.groups;
	for (var i=0; i<groups.length; ++i) {
		html+= groups[i].name;
		html+= '<input type="checkbox" class="moderators" name="moderators-'
			+data.id+'[]"';
		if (groups[i].id==1) {
			html+= ' checked="checked"';
		}
		html+= '  value="'+groups[i].id+'" /><br />';
	}
	html+= '<a href="javascript:;" class="add-group" '
		+'id="add-group-link-for-forum-'+data.id+'">[+]</a>';
	html+= '<td><a href="javascript:;" class="delete-forum-link" '
		+ 'id="delete-forum-'+data.id+'">[x]</a>';
	html+= '</td></tr>';
	$('#forum-moderators-table').append(html);
}
function forums_admin_update_page(data) {
	if (!data.status) {
		return alert(data.message);
	}
	$('#forum-'+data.id).remove();
	var postsTable = window.postsForModeration;
	var posts = data.posts;
	for (i=0; i<posts.length; ++i) {
		var row = $('#post-for-moderation-'+posts[i]);
		var pos = postsTable.fnGetPosition(($(row)[0]));
		if (pos!==null) {
			postsTable.fnDeleteRow(pos);
		}
	}
}
function forums_admin_update_groups(data) {
	$('.add-group').each(function() {
		var $this = $(this);
		var forum = $this.attr('id')
			.replace('add-group-link-for-forum-', '');
		var html = '<div>'+data.name;
		html+= '<input type="checkbox" class="moderators" '
			+'name="moderators-'+forum+'[]"';
		if (forum==data.forum) {
			html+= ' checked="checked"';
		}
		html+= ' /></div>';
		$(html).insertBefore($this);
	});
}
function forums_admin_update_posts(data) {
	alert('The moderater groups for this forum have been updated');
	if (data.posts) {
		var posts = data.posts;
		var table = window.postsForModeration;
		for (var i=0; i<posts.length; ++i) {
			var row = $('#post-for-moderation-'+posts[i]);
			var pos = table.fnGetPosition(row[0]);
			table.fnDeleteRow(pos);
		}
	}
}
function forums_admin_remove_post(data) {
	if (!data.status) {
		return alert('There was an error in serving your request');
	}
	var postsTable = window.postsForModeration;
	var id = data.id;
	var action = data.action;
	var pos = postsTable.fnGetPosition(($('#post-for-moderation-'+id)[0]));
	postsTable.fnDeleteRow(pos);
}
$(function() {
	var params={};
	if (jsvars.datatables['forum-datatable-requires-moderation']) {
		params["iDisplayLength"]=jsvars.datatables['forum-datatable-requires-moderation'].show;
	}
	window.postsForModeration = $('#forum-datatable-requires-moderation')
		.dataTable({});
	$('.approve').click(function() {
		var id = $(this).attr('id').replace('approve_', '');
		$.post('/a/p=forum/f=adminPostApprove/id='+id,
			forums_admin_remove_post,
			'json'
		);
	});
	$('.delete').click(function() {
		var id = $(this).attr('id').replace('delete_', '');
		if (confirm('Are you sure you want to delete this post')) {
			$.post('/a/p=forum/f=adminPostDelete/id='+id,
				forums_admin_remove_post,
				'json'
			);
		}
	});
	$('.moderators').live('change', function() {
		var $this = $(this);
		var checked = $this.attr('checked');
		var forum = $this.attr('name').replace('moderators-', '');
		forum = forum.replace('[]', '');
		var autoApprove = false;
		if (!checked) {
			var allUnchecked = true;
			$('input[name="moderators-'+forum+'[]"]').each(function() {
				if ($(this).attr('checked')) {
					allUnchecked = false;
					return false;
				}
			});
			if (allUnchecked) {
				var confirmText = 'You have removed all moderator groups for this '
					+'forum\nDo you want to auto approve all posts';
				autoApprove = confirm(confirmText);
			}
		}
		var group = $this.val();
		$.post('/a/p=forum/f=adminGroupModeratorSet', {
					"action": checked,
					"forum":forum,
					"group":group,
					"autoApprove":autoApprove
				},
				forums_admin_update_posts,
				'json'
		);
	});
	$('.add-group').live('click', function() {
		var $this = $(this);
		var id = $this.attr('id').replace('add-group-link-for-forum-', '');
		var html='<div>'
		html+= '<input class="new-group" id="new-moderator-group-for-forum-'+id
			+' />';
		html+= '</div>';
		$(html).insertBefore($this);
		$('.new-group').blur(function() {
			var $this = $(this);
			var groupName = $this.val();
			if (!groupName) {
				return alert('No name entered');
			}
			var forumID = $this.attr('id')
				.replace('new-moderator-group-for-forum-', '');
			$.post('/a/p=forum/f=adminGroupNew/name='+groupName+'/forum='+forumID,
				forums_admin_update_groups,
				'json'
			);
			$this.remove();
		});
	});
	$('.delete-forum-link').live('click', function() {
		if (confirm('Are you sure you want to delete this forum')) {
			var id = $(this).attr('id').replace('delete-forum-', '');
			$.post('/a/p=forum/f=adminForumDelete/id='+id,
				forums_admin_update_page,
				'json'
			);
		}
	});
	$('.add-forum').click(function() {
		html='<span><input class="new-forum" id="new-forum" /></span>';
		$(html).insertBefore(this);
		var page = $(this).attr('page');
		$('.new-forum').blur(function() {
			var $this = $(this);
			var name = $this.val();
			if (!name) {
				return alert('No name entered');
			}
			$this.remove();
			$.post('/a/p=forum/f=adminForumAdd', {
					'name':name,
					'page':page
				},
				forums_admin_update_forums_table,
				'json'
			);
		});
	});
});
