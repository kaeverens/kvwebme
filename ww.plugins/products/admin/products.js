$(function() {
	var $table=$('#products-list').dataTable({
		"iDisplayLength":10,
		"bProcessing": true,
		"bJQueryUI": true,
		"bServerSide": true,
		"bAutoWidth": false,
		"sAjaxSource": '/a/p=products/f=adminProductsListDT',
		"aoColumns": [
			{"sWidth":"4%"}, {"sWidth":"60%"}, {"sWidth":"10%"}, {"sWidth":"4%"},
			{"sWidth":"10%"}, {"sWidth":"4%"}, {"sWidth":"4%"}, {"sWidth":"4%"}],
		"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
			var id=+aData[5];
			nRow.id='product-row-'+id;
			$('td:nth-child(1)', nRow).html(+aData[0]
				?'<div title="has images" class="ui-icon ui-icon-image"/>'
				:'');
			$('td:nth-child(3),td:nth-child(4),td:nth-child(7)', nRow)
				.css('cursor', 'pointer');
			$('td:nth-child(2)', nRow)
				.css('cursor', 'pointer')
				.addClass('link')
				.click(function() {
					var id=$(this).closest('tr').attr('id').replace(/product-row-/, '');
					document.location='/ww.admin/plugin.php?_plugin='
						+'products&_page=products-edit&id='+id;
				});
			$('td:nth-child(8)', nRow).html(
				'<a class="delete-product" href="#" title="delete">[x]</a>'
			);
			return nRow;
		}
	});
	$('a.delete-product').live('click', function(){
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
	$('#products-list td').live('click', function() {
		var $this=$(this),$tr=$this.closest('tr');
		if ($this.attr('in-edit')) {
			return false;
		}
		$this.attr('in-edit', true);
		var id=+$tr.attr('id').replace('product-row-', '');
		switch($tr.find('td').index($this)) {
			case 2: // { stock number
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
			case 3: // { stockcontrol_total
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
			case 6: // { enabled
				var oldVal=$this.text()=='Yes'?1:0;
				var $inp=$('<select/>')
					.append('<option value="1">Yes</option>')
					.append('<option value="0">No</option>')
					.val(oldVal)
					.blur(function() {
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
		return false;
	});
});
