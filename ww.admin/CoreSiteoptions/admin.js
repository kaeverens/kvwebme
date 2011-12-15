function CoreSiteoptions_screen(page) {
	Core_sidemenu(
		[ 'General', 'Users', 'Themes', 'Plugins', 'Cron', 'Languages' ],
		'CoreSiteoptions',
		page
	);
	window['CoreSiteoptions_screen'+page]();
}
function CoreSiteoptions_screenCron() {
	var $content=$('#content').empty();
	$.post('/a/f=adminCronGet', function(ret) {
		var table='<table><thead><tr><th>Name</th><th>Period</th>'
			+'<th>Next</th><th>Description</th></tr></thead>'
			+'<tbody>';
		for (var i=0;i<ret.length;++i) {
			var cron=ret[i];
			table+='<tr cid="'+cron.id+'"><td>'+cron.name+'</td><td class="clickable">'
				+cron.period_multiplier+' '+cron.period+'</td><td class="clickable"'
				+'>'+cron.next_date+'</td><td>'+cron.notes+'</td></tr>';
		}
		table+='</tbody></table>';
		var $table=$(table)
			.appendTo($content);
		$table.dataTable();
		$table.find('td')
			.click(tdClicked);
	});
	function tdClicked() {
		var $this=$(this);
		if ($this.attr('clicked')) {
			return;
		}
		var $tr=$this.closest('tr');
		var id=+$tr.attr('cid');
		switch ($tr.find('td').index($this)) {
			case 1: // {
				var parts=$this.text().split(' ');
				var periods=['minute', 'hour', 'day', 'week', 'month', 'year'];
				var html='<select>';
				for (var i=1;i<32;++i) {
					html+='<option>'+i+'</option>';
				}
				html+='</select><select>';
				for (var i=0;i<periods.length;++i) {
					html+='<option>'+periods[i]+'</option>';
				}
				html+='</select>';
				$this
					.html(html)
					.find('select')
					.change(function() {
						var mult=$this.find('select:first-child').val(),
							period=$this.find('select:last-child').val(),
							url='/a/f=adminCronSave/id='+id+'/field=period';
						$.post(url+'/value='+period);
						$.post(url+'_multiplier/value='+mult);
						$this.html(mult+' '+period).removeAttr('clicked');
					});
				$this.find('select:first-child').val(parts[0]);
				$this.find('select:last-child').val(parts[1]);
				break; // }
			case 2: // {
				var $inp=$('<input id="nextrecurrance'+id+'"/>')
					.val($this.text())
					.appendTo($this.empty());
				$inp
					.datetimepicker({
						dateFormat: 'yy-mm-dd',
						timeFormat: 'hh:mm',
						onClose: function(dateText, inst){
							var url='/a/f=adminCronSave/id='+id+'/field=next_date';
							$.post(url+'/value='+dateText);
							$this.html(dateText).removeAttr('clicked');
						}
					})
					.focus();
				break; // }
		}
		$this.attr('clicked', 1)
	}
}
function CoreSiteoptions_screenLanguages() {
	var $content=$('#content').empty();
	$.post('/a/f=languagesGet', function(languages) {
		var table='<table id="languages-table"><thead>'
			+'<tr><th>Name</th><th>Code</th>'
			+'<th>Default</th><th>&nbsp;</th></tr></thead>'
			+'<tbody>';
		for (var i=0;i<languages.length;++i) {
			var lang=languages[i];
			var links=['<a href="#" class="edit">edit</a>'];
			if (!(+lang.is_default)) {
				links.push('<a href="#" class="delete">[x]</a>');
			}
			table+='<tr cid="'+lang.id+'"><td>'+lang.name+'</td>'
				+'<td>'+lang.code+'</td><td>'+(+lang.is_default?'Yes':'')+'</td>'
				+'<td>'+links.join(', ')+'</td></tr>';
		}
		table+='</tbody></table>';
		var $table=$(table)
			.appendTo($content);
		$table.dataTable();
		$('<a href="#">Add Language</a>')
			.click(function() {
				$('<form id="languages-form"><table>'
					+'<tr><th>Name</th><td><input name="name"/></td></tr>'
					+'<tr><th>Code <a href="http://en.wikipedia.org/wiki/List_of_ISO_63'
					+'9-1_codes" target="_blank">#</a></th><td>'
					+'<input name="code" class="small"/></td></tr>'
					+'</table></form>'
				)
					.dialog({
						'modal':true,
						'close':function() {
							$('#languages-form').remove();
						},
						'buttons': {
							'Add': function() {
								$.post('/a/f=adminLanguagesAdd',
									$('#languages-form').serialize(),
									CoreSiteoptions_screenLanguages
								);
								$('#languages-form').remove();
							},
							'Cancel': function() {
								$('#languages-form').remove();
							}
						}
					});
				return false;
			})
			.appendTo($content);
		$('#languages-table .delete').click(function() {
			var id=$(this).closest('tr').attr('cid');
			if (!confirm('are you sure you want to delete this language?')) {
				return;
			}
			$.post(
				'/a/f=adminLanguagesDelete/id='+id,
				CoreSiteoptions_screenLanguages
			);
			return false;
		});
		$('#languages-table .edit').click(function() {
			var id=$(this).closest('tr').attr('cid');
			var language;
			for (var i=0;i<languages.length;++i) {
				if (languages[i].id==id) {
					language=languages[i];
				}
			}
			$('<form id="languages-form"><input name="id" type="hidden"/><table>'
				+'<tr><th>Name</th><td><input name="name"/></td></tr>'
				+'<tr><th>Code <a href="http://en.wikipedia.org/wiki/List_of_ISO_63'
				+'9-1_codes" target="_blank">#</a></th><td>'
				+'<input name="code" class="small"/></td></tr>'
				+'<tr><th>Is Default</th><td><select name="is_default">'
				+'<option value="0">No</option><option value="1">Yes</option>'
				+'</select></td></tr>'
				+'</table></form>'
			)
				.dialog({
					'modal':true,
					'close':function() {
						$('#languages-form').remove();
					},
					'buttons': {
						'Save': function() {
							$.post('/a/f=adminLanguagesEdit',
								$('#languages-form').serialize(),
								CoreSiteoptions_screenLanguages
							);
							$('#languages-form').remove();
						},
						'Cancel': function() {
							$('#languages-form').remove();
						}
					}
				});
			for (var k in language) {
				$('#languages-form *[name='+k+']').val(language[k]);
			}
			return false;
		});
	});
}
function CoreSiteoptions_screenStats() {
	var $content=$('#content').empty();
	$.post('/a/f=adminStatsGet', function(ret) {
		console.log(ret);
	});
}
