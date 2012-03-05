$('a.delete-product').live('click', function(){
	var $tr=$(this).closest('tr');
	var id=$tr[0].id.replace(/product-row-/, '');
	var name=$tr.find('td.edit-link>a').text();
	if (!confirm(
		'are you want to delete the product "'+name+'"?'
	)) {
		return false;
	};
	document.location='/ww.admin/plugin.php?_plugin=products&_page=products'
		+'&delete='+id;
});
$(function() {
	$('#products-list').dataTable({
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
			$('td:nth-child(2)', nRow)
				.css({
					'cursor':'pointer',
				})
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
});
