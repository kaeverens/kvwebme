$(function() {
	$('#meetings').dataTable({
		'bJQueryUI':true
	});
	$('#meetings').on('click', '.edit', function() {
		var id=$(this).closest('tr').attr('id').replace('meeting-', '');
		$.post('/a/p=meetings/f=adminMeetingGet', {
			'id':id
		}, meeting_edit);
	});
	$('#meetings').on('click', '.delete', function() {
		if (!confirm('Are you sure you want to cancel this meeting?')) {
			return;
		}
		var id=$(this).closest('tr').attr('id').replace('meeting-', '');
		$.post('/a/p=meetings/f=adminMeetingDelete', {
			'id':id
		}, function() {
			document.location=
				"/ww.admin/plugin.php?_plugin=meetings&_page=meetings";
		});
	});
	$('#meetings-create').click(function() {
		meeting_edit({
			'id':0,
			'meeting_time':'',
			'user_id':0,
			'customer_id':0,
			'form_id':0
		});
		return false;
	});
	function meeting_edit(m) {
		// { $dialog
		var html='<table>'
			+'<tr><th>Meeting Time</th><td><input id="meeting-time"'
			+' class="datetime"/></td></tr>'
			+'<tr><th>Who</th><td><select id="meeting-user_id"/></td></tr>'
			+'<tr><th>Is Meeting Who</th><td><select id="meeting-customer_id"/>'
			+'</td></tr>'
			+'<tr><th>Question List</th><td><select id="meeting-form_id"/></td></tr>'
			+'</table>';
		// }
		var $dialog=$(html).dialog({
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					var meeting_time=$('#meeting-time').val(),
						user_id=+$('#meeting-user_id').val(),
						customer_id=+$('#meeting-customer_id').val(),
						form_id=+$('#meeting-form_id').val();
					if (!meeting_time || user_id<1 || form_id<1 || customer_id<1) {
						return alert('you must fill in the whole form');
					}
					$.post('/a/p=meetings/f=adminMeetingEdit', {
						'id':m.id,
						'meeting_time':meeting_time,
						'user_id':user_id,
						'customer_id':customer_id,
						'form_id':form_id
					}, function() {
						document.location=
							"/ww.admin/plugin.php?_plugin=meetings&_page=meetings";
					});
				}
			}
		});
		$.post('/a/p=forms/f=adminFormsList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-form_id').html(opts)
				.val(m.form_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						$dialog.remove();
						form_edit({
							'id':0,
							'name':'Name of Questions Form',
							'fields':[]
						});
					}
				});
		});
		$.post('/a/p=meetings/f=adminCustomersList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-customer_id').html(opts)
				.val(m.customer_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						var name=prompt("what is the customer's name");
						if (!name) {
							$('#meeting-customer_id').val('-1');
							return;
						}
						$.post('/a/p=meetings/f=adminCustomerCreate', {
							'name':name
						}, function(ret) {
							$('#meeting-customer_id')
								.append('<option value="'+ret.id+'">'+name+'</option>')
								.val(ret.id);
						});
					}
				});
		});
		$.post('/a/p=meetings/f=adminEmployeesList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-user_id').html(opts)
				.val(m.user_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						var name=prompt("what is the employee's name");
						if (!name) {
							$('#meeting-user_id').val('-1');
							return;
						}
						$.post('/a/p=meetings/f=adminEmployeeCreate', {
							'name':name
						}, function(ret) {
							$('#meeting-user_id')
								.append('<option value="'+ret.id+'">'+name+'</option>')
								.val(ret.id);
						});
					}
				});
		});
		$('#meeting-time')
			.val(m.meeting_time)
			.blur()
			.datetimepicker({
				dateFormat: 'yy-mm-dd',
				timeFormat: 'hh:mm',
				onClose: function(dateText, inst){
				}
			});
	}
	function form_edit(f) {
		if (typeof f.fields=='string') {
			f.fields=eval('('+f.fields+')');
		}
		// { $dialog
		var html='<div id="popup-wrapper"><ul><li><a href="#popup-fields">Fields</a></li>'
			+'<li><a href="#popup-template">Template</a></li></ul>'
			+'<div id="popup-fields"><table>'
			+'<tr><th>Name</th><td><input id="dialog-name"/></td></tr>'
			+'<tr><th>Questions</th><td><table id="dialog-questions">'
			+'<thead><tr><th>Question</th><th>Type</th><th></th></tr></thead>'
			+'<tbody/></table></td></tr>'
			+'</table></div>'
			+'<div id="popup-template"><p>Note, that if you leave this blank'
			+', the form will be shown in default format.</p>'
			+'<textarea id="popup-template-val" style="width:100%;"/>'
			+'</div>'
			+'</div>';
		var $dialog=$(html).dialog({
			'modal':true,
			'close':function() {
				$('#popup-template-val').ckeditor(function(){
					this.destroy();
				});
				$dialog.remove();
			},
			'width':800,
			'height':500,
			'buttons':{
				'Save':function() {
					var fields=[];
					var $rows=$fieldsTable.find('tbody tr');
					$rows.each(function() {
						var $this=$(this);
						var name=$this.find('input').val(),
							type=$this.find('select').val();
						var extras=[];
						switch(type) {
							case 'select':case 'select-multiple':
								extras={
									'values':$this.find('textarea').val()
								};
							break;
						}
						if (name) {
							fields.push({
								'name':name,
								'type':type,
								'extras':extras
							});
						}
					});
					var template=$('#popup-template-val').val();
					$.post('/a/p=forms/f=adminFormEdit', {
						'id':f.id,
						'name':$('#dialog-name').val(),
						'fields':fields,
						'template':template
					}, function() {
						$('#popup-template-val').ckeditor(function(){
							this.destroy();
						});
						$dialog.remove();
					});
				}
			}
		});
		// }
		// { fields
		var $fieldsTable=$('#dialog-questions');
		for (var i=0;i<f.fields.length;++i) {
			var r=f.fields[i];
			addRow(r.name, r.type, r.extras);
		}
		function addRow(name, type, extras) {
			extras=extras||{};
			var types={
				'input':'single line of text',
				'textarea':'multiple lines of text',
				'select':'select from a list',
				'select-multiple':'select multiple items',
				'image':'photograph'
			};
			var thtml='<select>';
			$.each(types, function(k, v) {
				thtml+='<option value="'+k+'">'+v+'</option>';
			});
			thtml+='</select>';
			var $row=$(
				'<tr><th><input/></th><td>'+thtml+'</td><td class="extras"/></tr>')
				.appendTo($fieldsTable);
			$row.find('input').val(name);
			$row.find('select')
				.val(type)
				.change(function() {
					switch ($(this).val()) {
						case 'select':
							$('<textarea/>').appendTo($row.find('.extras'))
								.val(extras.values);
						break;
						case 'select-multiple':
							$('<textarea/>').appendTo($row.find('.extras'))
								.val(extras.values);
						break;
						default:
							$row.find('.extras').empty();
						break;
					}
				})
				.change();
		}
		$fieldsTable.on('blur', 'input,select', function() {
			var $rows=$fieldsTable.find('tbody tr');
			$rows.each(function() {
				var $this=$(this);
				if (!$this.find('input').val()) {
					$this.remove();
				}
			});
			addRow('', '');
		});
		$('#dialog-name').val(f.name);
		addRow('', '');
		// }
		// { template
		$('#popup-template-val')
			.val(f.template)
			.ckeditor(CKEditor_config);
		// }
		$('#popup-wrapper').tabs();
	}
	$('#meeting-forms').dataTable({
		'bJQueryUI':true
	});
	$('#meeting-forms').on('click', '.edit', function() {
		var id=$(this).closest('tr').data('meeting-id');
		$.post('/a/p=forms/f=adminGet/id='+id, form_edit);
		return false;
	});
	$('#meeting-forms').on('click', '.meetings-view', function() {
		var id=$(this).closest('tr').data('meeting-id');
		$.post('/a/p=forms/f=adminGet/id='+id, function(ret) {
			var table='<table id="meetings-view"><thead><tr>';
			var fields=eval('('+ret.fields+')');
			for (var i=0;i<fields.length;++i) {
				table+='<th>'+fields[i].name+'</th>';
			}
			table+='</tr></thead>';
			table+='<tbody></tbody></table>';
			$('#main').empty().append('<h1>Meetings</h1>')
				.append('<a href="/ww.admin/plugin.php?_plugin=meetings&_page=forms">'
					+'back to Forms</a>')
				.append(table);
			var params={
				"sAjaxSource":'/a/p=meetings/f=adminMeetingsDataGetDT/form_id='+id,
				"bProcessing":true,
				"bJQueryUI":true,
				"bServerSide":true,
				"fnRowCallback":function( nRow, aData, iDisplayIndex ) {
					for (var i=0;i<fields.length;++i) {
					console.log(fields[i]);
						switch (fields[i].type) {
							case 'image':
								if (aData[i]!='') {
									$('td:nth-child('+(i+1)+')', nRow)
										.html(
											'<a target="popup" href="data:image/jpeg;base64,'+aData[i]+'">'
											+'<img src="data:image/jpeg;base64,'+aData[i]+'"'
											+' style="max-width:128px;max-height:128px;"/></a>'
										);
								}
							break;
						}
					}
					return nRow;
				}
			};
			$('#meetings-view').dataTable(params);
		});
		return false;
	});
});
