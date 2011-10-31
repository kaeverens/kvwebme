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
		var table='<table>';
		// { lists
		table+='<tr><th>Lists</th><td>'
		if (ret.numlists) {
			table+='<strong>'+ret.numlists+'</strong> <a href="javascript:Core_screen(\'mailinglists\', \'Lists\');">[edit]</a>';
		}
		else {
			table+='<em>No lists found. Please <a href="javascript:Mailinglists_editList(0)">add one</a>.</em>';
		}
		table+='</td></tr>';
		// }
		// { subscribers
		table+='<tr><th>Subscribers</th><td>'
		if (ret.numpeople) {
		}
		else {
			table+='<em>No subscribers found. Please <a href="javascript:Mailinglists_addPerson()">add one</a>.</em>';
		}
		table+='</td></tr>';
		// }
		table+='</table>';
		$('#content').html(table);
	});
}
function Mailinglists_screenLists(ret) {
	$.post('/a/p=mailinglists/f=adminListsList', function(ret) {
		var html='<a href="javascript:Mailinglists_editList(0);">[add new list]</a>'
			+'<ul>';
		for (var i=0;i<ret.length;++i) {
			html+='<li><a href="javascript:Mailinglists_editList('+ret[i].id+')">'
				+ret[i].name+': '+ret[i].subscribers+' subscribers</a></li>';
		}
		html+='</ul>';
		$('#content').empty().append(html);
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
	console.log(ret);
	var engines=['MailChimp'];
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
