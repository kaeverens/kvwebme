$(function() {
	var $table=$('#products-list').dataTable({
		"iDisplayLength":100,
		"bProcessing": true,
		"bJQueryUI": true,
		"bServerSide": true,
		"bAutoWidth": false,
		"sAjaxSource": '/a/p=products/f=adminProductsListDT',
		"aoColumns": [
			{"sWidth":"4%", "bSortable":false}, {"sWidth":"4%"},  {"sWidth":"60%"},
			{"sWidth":"10%"}, {"sWidth":"4%"},
			{"sWidth":"10%"}, {"sWidth":"4%"}, {"sWidth":"4%"},
			{"sWidth":"4%", "bSortable":false}],
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			var id=+aData[6];
			nRow.id='product-row-'+id;
			$('td:nth-child(1)', nRow).html('<input type="checkbox"/>');
			$('td:nth-child(2)', nRow).html(+aData[1]
				?'<div title="has images" class="ui-icon ui-icon-image"/>'
				:'');
			$('td:nth-child(3)', nRow)
				.css('cursor', 'pointer')
				.addClass('link')
				.click(function() {
					var id=$(this).closest('tr').attr('id').replace(/product-row-/, '');
					document.location='/ww.admin/plugin.php?_plugin='
						+'products&_page=products-edit&id='+id;
				});
			$('td:nth-child(4),td:nth-child(5),td:nth-child(6),td:nth-child(8)', nRow)
				.css('cursor', 'pointer');
			$('td:nth-child(6)', nRow)
				.data('uid', aData[5].replace(/\|.*/, ''))
				.text(aData[5].replace(/.*\|/, ''));
			$('td:nth-child(9)', nRow).html(
				'<a class="delete-product" href="#" title="delete">[x]</a>'
			);
			return nRow;
		}
	});
	$('#products-list')
		.delegate('tbody input[type=checkbox]', 'click', function(e) {
			e.stopPropagation();
		})
		.delegate('td', 'click', function() {
			var $this=$(this),$tr=$this.closest('tr');
			if ($this.attr('in-edit')) {
				return false;
			}
			$this.attr('in-edit', true);
			var id=+$tr.attr('id').replace('product-row-', '');
			switch($tr.find('td').index($this)) {
				case 3: // { stock number
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': 'stock_number',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 4: // { stockcontrol_total
					var oldVal=$this.text();
					var $inp=$('<input style="width:100%;height:100%;"/>')
						.val(oldVal)
						.blur(function() {
							var newVal=$inp.val();
							$this.text(newVal).attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': 'stockcontrol_total',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
				case 5: // { owner
					var oldVal=+$this.data('uid');
					$.post('/a/f=adminUserNamesGet', function(ret) {
						var opts=['<option>unknown owner</option>'];
						$.each(ret, function(k, v) {
							opts.push('<option value="'+k+'">'+v+'</option>');
						});
						var $inp=$('<select/>')
							.html(opts.join(''))
							.val(oldVal)
							.change(function() {
								var newVal=+$inp.val();
								$this.text(ret[newVal])
									.attr('in-edit', null)
									.data('uid', newVal);
								if (newVal!=oldVal) {
									$.post('/a/p=products/f=adminProductEditVal', {
										'name': 'user_id',
										'val': newVal,
										'id': id
									});
								}
							})
							.appendTo($this.empty())
							.focus();
					});
				break; // }
				case 7: // { enabled
					var oldVal=$this.text()=='Yes'?1:0;
					var $inp=$('<select/>')
						.append('<option value="1">Yes</option>')
						.append('<option value="0">No</option>')
						.val(oldVal)
						.change(function() {
							var newVal=+$inp.val();
							$this.text(newVal?'Yes':'No').attr('in-edit', null);
							if (newVal!=oldVal) {
								$.post('/a/p=products/f=adminProductEditVal', {
									'name': 'enabled',
									'val': newVal,
									'id': id
								});
							}
						})
						.appendTo($this.empty())
						.focus();
				break; // }
			}
		})
		.delegate('a.delete-product', 'click', function(){
			var $tr=$(this).closest('tr');
			var id=$tr[0].id.replace(/product-row-/, '');
			var name=$tr.find('td.link').text();
			if (confirm(
				'are you sure you want to delete the product "'+name+'"?'
			)) {
				$.post('/a/p=products/f=adminProductDelete/id='+id, function() {
					$table.fnDraw();
				});
			};
			return false;
		});
	$('#products-selectall').click(function() {
		$('#products-list_wrapper input').attr(
			'checked',
			$(this).attr('checked')?true:false
		);
	});
	$('#products-action').change(function() {
		var val=+$(this).val();
		var $inps=$('#products-list_wrapper tbody input[type="checkbox"]');
		var ids=[];
		$inps.each(function() {
			if (!$(this).attr('checked')) {
				return;
			}
			var id=+$(this).closest('tr').attr('id').replace('product-row-', '');
			ids.push(id);
		});
		switch(val) {
			case 1: // {
				if (!confirm('Are you sure you want to delete these products?')) {
					return;
				}
				$.post('/a/p=products/f=adminProductsDelete/ids='+ids, function() {
					$('#products-action').val('0');
					$table.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
			case 2: // {
				$.post('/a/p=products/f=adminProductsDisable/ids='+ids, function() {
					$('#products-action').val('0');
					$table.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
			case 3: // {
				$.post('/a/p=products/f=adminProductsEnable/ids='+ids, function() {
					$('#products-action').val('0');
					$table.fnDraw(false);
					$('#products-selectall').attr('checked', false);
				});
			break; // }
		}
	});
});
