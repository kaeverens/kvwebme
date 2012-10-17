$(function() {
	$.post('/a/p=issue-tracker/f=typesGet', function(ret) {
		var opts=['<option value="0"> -- choose -- </option>'];
		$.each(ret, function(k, v) {
			opts.push('<option value="'+v.id+'">'+v.name+'</option>');
		});
		opts.push('<option value="-1"> -- add new -- </option>');
		$('#issue-type')
			.html(opts.join(''))
			.change(function() {
				var $this=$(this), val=$this.val(), $wrapper=$('#issue-fields-wrapper');
				switch (val) {
					case '0': // {
						$wrapper.html('no '+ITStrings.issue+' title chosen');
					break; // }
					case '-1': // {
						var name=prompt(
							'What name should the new '+ITStrings.issue+' title have?'
						);
						if (!name) {
							return $('#issue-type').val('0').change();
						}
						$.post('/a/p=issue-tracker/f=adminTypeNew', {
							'name': name
						}, function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							$('<option value="'+ret.id+'">'+name+'</option>')
								.insertBefore($this.find('option:last-child'));
							$this.val(ret.id).change();
						});
					break; // }
					default: showFields(val, $wrapper);
				}
			})
			.change();
	});
	$('#it-edit-all').multiselect({
		'close':function() {
			var opts=$(this).multiselect('getChecked');
			var vals=[];
			$.each(opts, function(k, v) {
				vals.push($(v).val());
			});
			$('#it-edit-all').val(vals);
		}
	});
	$('#it-see-all').multiselect({
		'close':function() {
			var opts=$(this).multiselect('getChecked');
			var vals=[];
			$.each(opts, function(k, v) {
				vals.push($(v).val());
			});
			$('#it-see-all').val(vals);
		}
	});
	function showFields(id, $wrapper) {
		$wrapper.empty();
		var $table=$('<table class="borders">'
			+'<thead><tr><th>Name</th><th>Type</th><th>&nbsp;</th></tr></thead>'
			+'<tbody/></table>')
			.appendTo($wrapper);
		var $body=$table.find('tbody');
		$('<button>Add Row</button>')
			.appendTo($wrapper)
			.click(function() {
				var name=prompt('What is the new field\'s name?');
				if (!name) {
					return false;
				}
				addRow($body, name, 'input');
				return false;
			});
		$('<button>Save</button>')
			.appendTo($wrapper)
			.click(function() {
				var fields=[];
				$body.find('>tr').each(function() {
					var $tr=$(this);
					fields.push({
						name:$('td.name', $tr).text(),
						type:$('td.type select', $tr).val()
					});
				});
				$.post('/a/p=issue-tracker/f=adminTypeSet', {
					'id':id,
					'fields':fields
				}, function(ret) {
					alert('saved');
				});
				return false;
			});
		$.post('/a/p=issue-tracker/f=adminTypeGet', {
			'id': id
		}, function(ret) {
			$.each(ret.fields, function(k, v) {
				addRow($body, v.name, v.type);
			});
		});
	}
	function addRow($body, name, type) {
		var $tr=$('<tr/>').appendTo($body);
		$tr.append('<td class="name">'+name+'</td>');
		$tr.append('<td class="type"></td>');
		$tr.append('<td class="delete"><a href="#">[x]</a></td>');
		$tr.find('.delete').click(function() {
			$tr.remove();
			return false;
		});
		setupType($tr, type);
	}
	function setupType($tr, type) {
		var types={
			'input':'text-field, single line',
			'textarea':'text-field, multiple lines',
			'date':'date',
			'file':'file'
		}
		var opts=[];
		$.each(types, function(k, v) {
			opts.push('<option value="'+k+'">'+v+'</option>');
		});
		$('<select>'+opts.join('')+'</select>')
			.appendTo($tr.find('td.type'))
			.val(type);
	}
});
