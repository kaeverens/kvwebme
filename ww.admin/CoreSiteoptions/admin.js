function CoreSiteoptions_screen(page) {
	Core_sidemenu(
		[ 'General', 'Users', 'Themes', 'Plugins', 'Cron' ],
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
