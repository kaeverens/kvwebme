function OnlineStore_screen(page) {
	window['OnlineStore_screen'+page]();
}
function OnlineStore_screenCustomerList() {
	var $content=$('#content');
	$('#content').html('<div><select id="users-group-filter"/>'
		+'<button>'+__('Add new user')+'</button>'
		+'<table id="users-list"><thead>'
		+'<tr><th>'+__('ID')+'</th><th>'+__('Name')+'</th><th>'+__('Email')+'</th><th>'+__('Phone')+'</th>'
		+'<th>'+__('Date Created')+'</th><th>'+__('Groups')+'</th><th>&nbsp;</th></tr>'
		+'</thead><tbody></tbody></table></div>')
	$('button', $content).click(function() {
		window.top.location="/ww.admin/siteoptions.php?page=users&id=-1";
	});
	$.post('/a/p=online-store/f=adminUserGroupsGet', function(ret) {
		var all=[], gopts=[];
		for (var i=0;i<ret.length;++i) {
			var g=ret[i];
			gopts.push('<option value="'+g.id+'">'+g.name+'</option>');
			all.push(g.id);
		}
		$('#users-group-filter')
			.html(
				// TODO: translation needed
				'<option value="'+all+'"> -- filter by group -- </option>'
				+gopts.join('')
			)
			.change(function() {
				window.openDataTable.fnDraw();
			});
		var params={
			"sAjaxSource": '/a/f=adminUsersGetDT',
			"bProcessing":true,
			"bJQueryUI":true,
			"bServerSide":true,
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
				var id=+aData[0];
				nRow.id='users-list-row-'+id;
				$('td:nth-child(2)', nRow).addClass('editable');
				$('td:nth-child(3)', nRow).addClass('editable');
				$('td:nth-child(4)', nRow).addClass('editable');
				$('td:nth-child(7)', nRow)
					.html('<a target="_top" href="/ww.admin/siteoptions.php'
						+'?page=users&id='+id+'">edit</a>');
				return nRow;
			},
			"fnServerData":function(sSource, aoData, fnCallback) {
				aoData.push({
					"name":"filter-groups",
					"value":$('#users-group-filter').val()
				});
				$.getJSON(sSource,aoData,fnCallback);
			}
		};
		window.openDataTable=$('#users-list')
			.dataTable(params);
		$('#users-list').on('click', 'td.editable', function() {
			var $this=$(this),$tr=$this.closest('tr');
			if ($this.attr('in-edit')) {
				return false;
			}
			$this.attr('in-edit', true);
			var id=+$tr.attr('id').replace('users-list-row-', '');
			switch($tr.find('td').index($this)) {
				case 1: // { name
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/f=adminUserEditVal', {
									'name': 'name',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 2: // { email
					var oldVal=$this.text();
					var $inp=$('<input type="email" style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/f=adminUserEditVal', {
									'name': 'email',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 3: // { phone
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/f=adminUserEditVal', {
									'name': 'phone',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
			}
			return false;
		});
	});
}
