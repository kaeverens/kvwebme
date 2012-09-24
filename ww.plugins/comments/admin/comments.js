$(function() {
	var params={'aaSorting':[[0,'desc']]};
	if (jsvars.datatables['comments-table']) {
		params["iDisplayLength"]=jsvars.datatables['comments-table'].show;
	}
	my_table = $("#comments-table").dataTable(params);
	if (noModeration) {
		my_table.fnSetColumnVis(5, false);
	}
	$('#no_moderation').change(function() {
		var $this=$(this);
		var val=$this.attr('checked');
		$.post('/ww.plugins/comments/admin/set_moderation.php',
			{
				"value":val
			},
			function(ret) {
				update_table_columns(ret);
				$('<span>'+__('saved')+'</span>').insertAfter($this).fadeOut('slow', function()	{
					$(this).remove();
				});
			},
			"json"
		);
	});
	$('#comments_moderatorEmail').change(function() {
		var $this=$('#comments_moderatorEmail');
		var val = $this.val();
		$.post('/ww.plugins/comments/admin/set_moderatorEmail.php',
			{
				"email":val
			},
			function(){
				$('<span>'+__('saved')+'</span>').insertAfter($this).fadeOut('slow', function()	{
					$(this).remove();
				});
			}
		);
	});
	$('#no_captchas').change(function() {
		var $this=$('#no_captchas');
		var val=$this.attr('checked');
		$.post('/a/p=comments/f=adminCaptchasSet',
			{
				"value":val
			},
			function() {
				$('<span>'+__('saved')+'</span>').insertAfter($this).fadeOut('slow', function()	{
					$(this).remove();
				});
			}
		);
	});
});
function start_edit(id, comment) {
	$('<textarea id="comment-edit">'+comment+'</textarea>')
	.dialog(
		{
			title:'Edit this comment below',
			modal:true,
			buttons: {
				'Save' : function() {
					$.post('/a/p=comments/f=adminEdit/id='+id, {
							"comment":$('#comment-edit').val()
						},
						update_comment,
						"json"
					);
				},
				'Cancel':function() {
					$(this).remove();
				}
			}
		}
	);
}
function start_delete(id, my_table) {
	if (confirm('Are you sure you want to delete this comment')) {
		$.post('/a/p=comments/f=adminDelete/id='+id, remove_row, "json");
	}
}
function remove_row(data) {
	if (!data.status) {
		return alert('Could not delete this comment');
	}
	var pos = my_table.fnGetPosition($('#comment-'+data.id)[0]);
	my_table.fnDeleteRow(pos);
}
function update_comment(data) {
	var pos = my_table.fnGetPosition(($('#comment-'+data.id))[0]);
	my_table.fnUpdate(data.comment, pos, 4);
	my_table.fnUpdate(
		'<a href="javascript:;" onclick='
		+'"start_edit('
			+data.id+','
			+'\''+data.comment+'\''
		+');"'
		+'>edit</a>',
		pos,
		6
	);
	$('#comment-edit').remove();
}
function update_table_columns(data) {
	switch (data.value) {
		case '1': // {
			my_table.fnSetColumnVis(5, false);
		break; // }
		case '0': // {
			my_table.fnSetColumnVis(5, true);
		break // }
	}
}
function start_moderation(id, val) {
	$.post('/a/p=comments/f=adminModerate', {"id":id, "value":val},
		update_moderation,
		"json"
	);
}
function update_moderation(data) {
	if (!data.status) {
		return alert(data.message);
	}
	var pos = my_table.fnGetPosition($('#comment-'+data.id)[0]);
	var val = 0;
	var approveString = '';
	switch (data.value) {
		case '0': // {
			val = 1;
			approveString = 'Approve';
		break;
		case '1': // {
			val = 0;
			approveString = 'Unapprove';
		break; // }
	}
	my_table.fnUpdate(
		'<a href="javascript:start_moderation('+data.id+', '+val+');">'
		+approveString+'</a>',
		pos,
		5
	);
}
