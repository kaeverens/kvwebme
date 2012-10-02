// TODO: translation of statusses needed
window.os_statuses=[
	[0, 'Unpaid'],
	[4, 'Authorised'],
	[1, 'Paid'],
	[2, 'Delivered'],
	[3, 'Cancelled']
];
function os_invoice(id, print){
	var w=$(window), wh=w.height(), ww=w.width(), p=print?'&print=1':'';
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/admin/show-invoice.php?id='+id+p+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
function os_listItems(id){
	var $d=$('<p>'+__('Getting list of ordered items - please wait...')+'</p>').dialog({
		"modal":true
	});
	$.post('/a/p=online-store/f=adminOrderItemsList/id='+id, function(ret){
		$d.remove();
		if (ret.error) {
			return alert(ret.error);
		}
		var html='<table><tr><th>'+__('Name')+'</th><th>'+__('Amount')+'</th></tr>', i=0;
		for (;i<ret.length;++i) {
			html+='<tr><td>'+ret[i].name+'</td><td>'+ret[i].amt+'</td></tr>';
		}
		html+='</table>';
		$(html).dialog({
			"modal":true,
			"close":function() {
				$(this).remove();
			}
		});
	});
}
function onlinestoreFormValues(id){
	var w=$(window), wh=w.height(), ww=w.width();
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/admin/show-details.php?id='+id+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
function onlinestoreFields(force){
	var i;
	if (!$('#online-stores-fields').length
		|| (!force && !window.ckeditor_body.checkDirty())
	) {
		return;
	}
	var $wrapper=$('#online-stores-fields').empty(),
		$form=$('<div id="online-stores-tester" style="display:none">'+window.ckeditor_body.getData()+'</div>').appendTo($wrapper);
	for(i in os_fields){
		if(typeof(os_fields[i])!="object"){
			continue;
		}
		os_fields[i].show=0;
	}
	var $inputs=$form.find('input, select, textarea'), c=0, to_show=[];
	for(i=0;i<$inputs.length;++i){
		if(!os_fields[$inputs[i].name]){
			os_fields[$inputs[i].name]={
				required:$($inputs[i]).attr('required')
			}
		}
		os_fields[$inputs[i].name].show=1;
		++c;
		to_show.push($inputs[i].name);
	}
	$wrapper.empty();
	if(!c){
		// TODO: translation needed
		$wrapper.append('<em>No fields defined. Please create a form in the Form tab.</em>');
	}
	else{
		// TODO: translation needed
		var table='<table id="online_stores_fields_table" style="width:100%">'
			+'<tr><th>'+__('Name')+'</th><th>'+__('Required')+'</th></tr>',
			$rows, $row, $cells;
		for(i=0;i<c;++i){
			table+='<tr><td></td><td></td><td></td></tr>';
		}
		$wrapper.append(table+'</table>');
		$rows=$wrapper.find('tr');
		for(i=0;i<c;++i){
			$row=$($rows[i+1]);
			$row.data('os_name', to_show[i]);
			$cells=$row.find('td');
			$($cells[0]).text(to_show[i]);
			$(
				'<input class="is-required" type="checkbox"'
				+(os_fields[to_show[i]].required?' checked="checked"':'')
				+' />'
			).appendTo($cells[1]);
		}
	}
	$('<input id="online_stores_fields_input" type="hidden" name="page_vars[online_stores_fields]" />').val(Json.toString(os_fields)).appendTo($wrapper);
}
function onlinestoreFieldsUpdate(){
	var name=$(this).closest('tr').data('os_name');
	if(this.className=='is-required'){
		os_fields[name].required=this.checked?1:0;
	}
	$('#online_stores_fields_input').val(Json.toString(os_fields));
}
function onlinestoreStatus(id, current_status){
	var options=[];
	for(var i=0;i<window.os_statuses.length;++i){
		var key=window.os_statuses[i][0], val=window.os_statuses[i][1];
		var html='<option value="'+key+'"';
		if(key==current_status){
			html+=' selected="selected"';
		}
		html+='>'+__(val)+'</option>';
		options.push(html);
	}
	var target=$('#os_status_'+id);
	$('<select id="os_status_select_'+id+'">'+options.join('')+'</select>')
		.change(function(){
			var text=$(this).find(':selected').text();
			var id=this.id.replace(/os_status_select_/, ''), val=+$(this).val();
			$.post('/a/p=online-store/f=adminChangeOrderStatus/id='+id+'/status='+val, function(){
				$('#os_status_select_'+id).replaceWith(
					$('<a id="os_status_'+id+'" href="javascript:;">'
						+text+'</a>')
						.click(function(){
							onlinestoreStatus(id, val);
						})
				);
			});
		})
		.insertAfter(target);
	target.remove();
}
$('#online_stores_fields_table input').live('click', onlinestoreFieldsUpdate);

$(function(){
	$('.tabs').tabs();
	$('#online-store-status').change(function(ev){
		document.location='/ww.admin/plugin.php?_plugin=online-store&_page=orders'
			+'&online-store-status='+$(ev.target).val();
	});
	onlinestoreFields();
	$('.ui-tabs-nav').live('mousedown', onlinestoreFields);
	$('form').bind('submit', onlinestoreFields);
	$("#online_store_redirect_to, #online_store_quickpay_redirect_to, #online_store_quickpay_redirect_failed")
		.remoteselectoptions({url:"/a/f=adminPageParentsList"});
	var $checkout_type=$('select[name="page_vars[onlinestore_viewtype]"]');
	$checkout_type.change(function() {
		switch(+$(this).val()) {
			case 2: case 3: // {
				$('.online-store-checkout-form').css('display', 'none');
			break; // }
			default: // {
				$('.online-store-checkout-form').css('display', 'block');
			// }
		}
	}).change();
	$('#online-store-export-button').click(function() {
		var cdate=$('#online-store-export-from').val();
		if (!cdate) {
			// TODO: translation needed
			return alert('You must enter a date');
		}
		document.location='/a/p=online-store/f=adminOrdersExport/cdate='+cdate;
		return false;
	});
	$('#online-store-export-from').datepicker({
		dateFormat: 'yy-mm-dd'
	});
	$.post('/a/f=adminUserGroupsGet', function(ret) {
		var names=[];
		for (var i=0;i<ret.length;++i) {
			names.push(ret[i].name);
		}
		$('#onlinestore-customersUsergroup').autocomplete({
			'source': names
		});
	});
	$('#onlinestore-orders-table').dataTable({
		'bJQueryUI':true,
		"iDisplayLength": 100
	});
	$('#onlinestore-orders-action').change(function() {
		var val=+$(this).val();
		var $inps=$('#onlinestore-orders-table tbody input[type="checkbox"]');
		var ids=[];
		$inps.each(function() {
			if (!$(this).attr('checked')) {
				return;
			}
			var id=+$(this).closest('tr').data('id');
			ids.push(id);
		});
		if (val<4) {
			for (var j=0;j<ids.length;++j) {
				var id=ids[j];
				onlinestoreStatus(id, val-1);
				$('#os_status_select_'+id).change();
			}
		}
		if (val==4) {
			document.location='/a/p=online-store/f=adminInvoicesGetAsPdf&ids='+ids;
		}
		$(this).val(0);
	});
	$('#onlinestore-orders-selectall').click(function() {
		$('#onlinestore-orders-table input[type=checkbox]').attr(
			'checked',
			$(this).attr('checked')?true:false
		);
	});
	var idOSAuthorised='#online-store-authorised ',
		idOSCountries='#online-store-countries ';
	$(idOSAuthorised+'th input').change(function(){
		$(idOSAuthorised+'td input')
			.attr('checked', $(this).is(':checked'));
	});
	$(idOSAuthorised+'input[type="button"]').click(function() {
		var txns=[];
		$(idOSAuthorised+'td input:checked').each(function() {
			txns.push($(this).attr('id').replace(/auth/, ''));
		});
		if (!txns.length) {
			// TODO: translation needed
			return alert('no transactions selected');
		}
		$.post('/a/p=online-store/f=adminCapture/ids='+txns, function(ret) {
			var i=0, ok=ret.ok;
			if (ok.length) {
				for (;i<ok.length;++i) {
					$('#capture'+ok[i]).remove();
				}
				// TODO: translation needed
				alert(ok.length+' transactions successfully captured');
			}
			if (ret.errors.length) {
				alert(ret.errors.join("\n\n"));
			}
			document.location="/ww.admin/pages/form.php?id="+$('input[name=id]').val();
		});
	});
	$(idOSCountries+'a.all,'+idOSCountries+'a.none')
		.click(function() {
			$(this).siblings('table').find('input')
				.attr('checked', $(this).is('.all'));
			return false;
		});
	if (!$(idOSCountries+'input:checked').length) {
		$(idOSCountries+'input').attr('checked', true);
	}
});

function onlinestoreCustomers() {
	var $dialog=$('<div><select id="users-group-filter"/>'
		+'<button>'+__('Add new user')+'</button>'
		+'<table id="users-list"><thead>'
		+'<tr><th>'+__('ID')+'</th><th>'+__('Name')+'</th><th>'+__('Email')+'</th><th>'+__('Phone')+'</th>'
		+'<th>'+__('Date Created')+'</th><th>'+__('Groups')+'</th><th>&nbsp;</th></tr>'
		+'</thead><tbody></tbody></table></div>')
		.dialog({
			'modal':true,
			'minWidth':700,
			'minHeight':500,
			'close': function() {
				$dialog.remove();
			}
		});
	$('button', $dialog).click(function() {
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
						+'?page=users&id='+id+'">'+__('Edit')+'</a>');
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
