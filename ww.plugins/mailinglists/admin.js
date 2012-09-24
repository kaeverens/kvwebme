function Mailinglists_screen(page) {
	Core_sidemenu(
		[ 'Dashboard', 'People', 'Lists' ],
		'mailinglists',
		page
	);
	window['Mailinglists_screen'+page]();
}
function Mailinglists_screenDashboard() {
	$.post('/a/p=mailinglists/f=adminGetDashboardInfo', function(ret) {
		var table='<table style="width:100%">';
		// { lists
		table+='<tr><th>'+__('Lists')+'</th><td>'
		if (ret.numlists) {
			table+='<strong>'+ret.numlists+'</strong> <a href="javascript:Core_screen(\'mailinglists\', \'Lists\');">[edit]</a>';
		}
		else {
			table+='<em>No lists found. Please <a href="javascript:Mailinglists_editList(0)">add one</a>.</em>';
		}
		table+='</td></tr>';
		// }
		// { subscribers
		table+='<tr><th>'+__('Subscribers')+'</th><td>'
		if (ret.numpeople) {
		}
		else {
			table+='<em>No subscribers found. Please <a href="javascript:Mailinglists_addPerson()">add one</a>.</em>';
		}
		table+='</td></tr>';
		// }
		// { automated issue creation
		table+='<tr><th>Automated issue creation</th><td>'
			+'<table id="mailinglists-automated-issue-sending" style="width:100%">'
			+'<thead><tr><th>Period</th><th>List</th><th>Template</th>'
			+'<th>Next Issue</th><th>Active</th><th>&nbsp;</th></thead><tbody/></tr>'
			+'</table><button id="automated-issue-create">'
			+__('Add New Automated Issue')+'</button></td></tr>';
		// }
		table+='</table>';
		$('#content').html(table);
		function updateRows(ret) {
			console.log(ret);
		}
		$('#automated-issue-create').click(function() {
			$.post('/a/p=mailinglists/f=adminAutomatedIssuesEdit/id=-1', function() {
				$automatedIssuesTable.fnDraw(1);
			});
		});
		var params={
			"sAjaxSource": '/a/p=mailinglists/f=adminAutomatedIssuesListDT',
			"bFilter":false,
			"bProcessing":true,
			"bJQueryUI":true,
			"bServerSide":true,
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
				$('td:nth-child(1)', nRow)
					.html(__(['', 'Hour', 'Day', 'Week', 'Month', 'Year'][+aData[0]]));
				$('td:nth-child(5)', nRow).html(__(aData[4]=='0'?'No':'Yes'));
				$('td:nth-child(6)', nRow).html(
					'<a href="#" class="edit">'+__('Edit')+'</a>'
					+'&nbsp;|&nbsp;<a href="#" class="delete">'+__('[x]')+'</a>'
				);
				$(nRow).data('id', aData[5]);
				return nRow;
			}
		};
		var $automatedIssuesTable=$('#mailinglists-automated-issue-sending')
			.dataTable(params);
		$automatedIssuesTable.on('click', '.delete', function() {
			var id=$(this).closest('tr').data('id');
			if (!confirm('Are you sure you want to remove this automated issue?')) {
				return;
			}
			$.post(
				'/a/p=mailinglists/f=adminAutomatedIssueDelete/id='+id,
				function() {
					$automatedIssuesTable.fnDraw(1);
				}
			);
		});
	});
}
function Mailinglists_screenLists(ret) {
	$.post('/a/p=mailinglists/f=adminListsList', function(ret) {
		var html='<a href="javascript:Core_screen(\'mailinglists\',\'Dashboard\');">'
			+__('Return to Dashboard')+'</a>'
			+'<table id="mailinglists-table"><tr><th>'+__('Name')+'</th>'
			+'<th>'+__('Subscribers')+'</th><th>'+__('Mail Engine')+'</th>'
			+'<th>&nbsp;</th></tr>';
		for (var i=0;i<ret.length;++i) {
			html+='<tr data-mid="'+ret[i].id+'"><th>'+ret[i].name+'</th>'
				+'<td>'+ret[i].subscribers+'</td>'
				+'<td>'+ret[i].meta.engine+'</td>'
				+'<td><a href="#" class="edit">edit</a>'
				+' | <a href="#" class="delete">delete</a></td></tr>'
		}
		html+='<tr data-mid="0"><td colspan="4"><button class="edit">'
			+__('Add New List')+'</button></td></tr></table>';
		$('#content').empty().append(html);
		$('#mailinglists-table').on('click', '.edit', function() {
			var id=$(this).closest('tr').data('mid');
			Mailinglists_editList(id);
		});
		$('#mailinglists-table').on('click', '.delete', function() {
			var $row=$(this).closest('tr');
			var id=$row.data('mid');
			if (!confirm('Are you sure you want to remove this mailing list?')) {
				return;
			}
			$.post(
				'/a/p=mailinglists/f=adminListDelete/id='+id,
				function() {
					$row.remove();
				}
			);
		});
	});
}
function Mailinglists_editList(ret) {
	if (+ret===ret) {
		if (ret) {
			return $.post('/a/p=mailinglists/f=adminListDetails/id='+ret, Mailinglists_editList);
		}
		ret={
			"name":"default",
			"id":0,
			"meta":{
				"engine":""
			}
		};
	}
	var engines=['MailChimp', 'Ubivox'];
	var $form=$('<div><input type="hidden" id="mailinglist-id" value="'+ret.id+'"/><table>'
		+'<tr><th>List Name</th><td><input id="mailinglist-name"/></td></tr>'
		+'<tr><th>Mailer to use</th><td><select id="mailinglist-meta-engine"><option></option></td></tr>'
		+'<tr><th>Mailer Details</th><td id="mailinglist-meta-engine-details"/></td></tr>'
		+'</table>').appendTo(document.body);
	$('#mailinglist-name').val(ret.name);
	// { engines
	function switchEngine() {
		switch ($('#mailinglist-meta-engine').val()) {
			case 'MailChimp': // {
				var html='<a href="http://mailchimp.com/" target="_blank" class="external">MailChimp website</a>'
					+'<table>'
					+'<tr><th>API-Key</th><td><input id="mailinglist-meta-mailchimp-apikey"/></td></tr>'
					+'<tr><th>List to link to</th><td><select id="mailinglist-meta-mailchimp-list"><option value="0"> -- please choose -- </option></td></tr>'
					+'</table>';
				$('#mailinglist-meta-engine-details').html(html);
				$('#mailinglist-meta-mailchimp-apikey').val(ret.meta['mailchimp-apikey']||'');
				if (ret.meta['mailchimp-list']) {
					var v=ret.meta['mailchimp-list'].replace(/.*\|/, '');
					$('#mailinglist-meta-mailchimp-list')
						.append('<option value="'+ret.meta['mailchimp-list']+'">'+v+'</option>')
						.val(ret.meta['mailchimp-list']);
				}
				$('#mailinglist-meta-mailchimp-list')
					.remoteselectoptions({
						"url":'/a/p=mailinglists/f=adminListsGetMailChimp',
						"other_GET_params":function() {
							return $('#mailinglist-meta-mailchimp-apikey').val();
						},
						"errors":function() {
							return alert('error retrieving mailing lists. is your API key correct?');
						}
					});
			break; // }
			case 'Ubivox': // {
				var html='<a href="http://www.ubivox.com/" target="_blank" class="external">Ubivox website</a>'
					+'<table>'
					+'<tr><th>API Username</th><td><input id="mailinglist-meta-ubivox-apiusername"/></td></tr>'
					+'<tr><th>API Password</th><td><input type="password" id="mailinglist-meta-ubivox-apipassword"/></td></tr>'
					+'<tr><th>List to link to</th><td><select id="mailinglist-meta-ubivox-list"><option value="0"> -- please choose -- </option></td></tr>'
					+'</table>';
				$('#mailinglist-meta-engine-details').html(html);
				$('#mailinglist-meta-ubivox-apiusername').val(ret.meta['ubivox-apiusername']||'');
				$('#mailinglist-meta-ubivox-apipassword').val(ret.meta['ubivox-apipassword']||'');
				if (ret.meta['mailchimp-list']) {
					var v=ret.meta['mailchimp-list'].replace(/.*\|/, '');
					$('#mailinglist-meta-mailchimp-list')
						.append('<option value="'+ret.meta['mailchimp-list']+'">'+v+'</option>')
						.val(ret.meta['mailchimp-list']);
				}
				$('#mailinglist-meta-ubivox-list')
					.remoteselectoptions({
						"url":'/a/p=mailinglists/f=adminListsGetUbivox',
						"other_GET_params":function() {
							return $('#mailinglist-meta-ubivox-apiusername').val()
								+'|'+$('#mailinglist-meta-ubivox-apipassword').val();
						},
						"errors":function() {
							return alert('error retrieving mailing lists.');
						}
					});
			break; // }
			default: // {
				$('#mailinglist-meta-engine-details').empty();
			// }
		}
	}
	var $select=$('#mailinglist-meta-engine');
	for (var i=0;i<engines.length;++i) {
		$select.append('<option>'+engines[i]+'</option>');
	}
	$select
		.val(ret.meta.engine);
	$select
		.change(switchEngine)
		.change();
	// }
	$form.dialog({
		"modal":true,
		"width":415,
		"close":function() {
			$form.remove();
		},
		"buttons":{
			"Save":function() {
				var vals={}, meta={};
				$form.find('input,select').each(function() {
					var $this=$(this);
					var k=$this.attr('id').replace(/^mailinglist-/, '');
					if (/^meta-/.test(k)) {
						meta[k.replace(/^meta-/, '')]=$this.val();
					}
					else {
						vals[k]=$this.val();
					}
				});
				$.post('/a/p=mailinglists/f=adminListSave', {
					"vals": vals,
					"meta": meta
				}, function(ret) {
					$form.remove();
					switch(window.current_screen) {
						case 'mailinglists|Dashboard': // {
							return Mailinglists_screenDashboard();
							// }
						case 'mailinglists|Lists': // {
							return Mailinglists_screenLists();
							// }
					}
				});
			}
		}
	});
}
