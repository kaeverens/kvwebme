$(function() {
	$('#ads-table').dataTable({
		'bJQueryUI':true,
		'aaSorting':[[0, 'desc']]
	}).fnSetFilteringDelay();
	$('#ads-table').on('click', '.mark-as-purchased', function() {
		$.post('/a/p=ads/f=adminOrderMarkPaid', {
			'item_number':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			document.location="/ww.admin/plugin.php?_plugin=ads&_page=unpaid-ads";
		});
		return false;
	});
	$('#ads-table').on('click', '.delete', function() {
		if (!confirm('are you sure you want to delete this ad?')) {
			return false;
		}
		$.post('/a/p=ads/f=adminAdUnpaidDelete', {
			'id':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			document.location="/ww.admin/plugin.php?_plugin=ads&_page=unpaid-ads";
		});
		return false;
	});
});
