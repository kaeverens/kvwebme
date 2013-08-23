$(function() {
	$('#onlineStoreEbay-importOrders').click(function() {
		var $dialog=$('<p>Please wait - checking</p>')
			.dialog({
				'modal':true,
				'close':function() {
					$dialog.remove();
				}
			});
		$.post('/a/p=online-store-ebay/f=adminImportOrders', function(ret) {
			$dialog.remove();
			if (ret.errors) {
				alert('there were errors in the reply. please check the console for details');
				console.log(ret);
			}
			alert(ret.imported+' orders imported');
			if (ret.imported) {
				$('#online-store-status').val(1).change();
			}
			console.log(ret);
		});
	});
});
