$(function() {
	$('#ads-table').dataTable({
		'bJQueryUI':true
	});
	$('#ads-types-table').dataTable({
		'bJQueryUI':true
	});
	$('#ads-types-table').on('click', '.delete', function() {
		if (!confirm('are you sure you want to delete this ad type?')) {
			return false;
		}
		$.post('/a/p=ads/f=adminTypesDelete', {
			'id':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			document.location="/ww.admin/plugin.php?_plugin=ads&_page=ad-types";
		});
		return false;
	});
	$('#ads-types-table').on('click', '.edit', function() {
		$.post('/a/p=ads/f=typeGet', {
			'id':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, editAdsType);
		return false;
	});
	$('#ads-table').on('click', '.edit', function() {
		$.post('/a/p=ads/f=adminAdGet', {
			'id':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, editAd);
		return false;
	});
	$('#ads-table').on('click', '.delete', function() {
		if (!confirm('are you sure you want to delete this ad?')) {
			return false;
		}
		$.post('/a/p=ads/f=adminAdDelete', {
			'id':$(this).closest('tr').attr('id').replace(/.*-/, '')
		}, function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			document.location="/ww.admin/plugin.php?_plugin=ads&_page=ads";
		});
		return false;
	});
	$('.new-ad').click(function() {
		var ret={
			'id':0,
			'name':'new ad'
		};
		editAd(ret);
		return false;
	});
	$('.new-ads-type').click(function() {
		var ret={
			'id':0,
			'width':234,
			'height':60,
			'name':'half banner',
			'price_per_day':0
		};
		editAdsType(ret);
		return false;
	});
	function editAd(ret) {
		var html='<table>'
			+'<tr><th>Name</th><td><input id="popup-name"/></td>'
			+'<th>Image URL</th><td style="width:300px"><input id="popup-image_url"/></td></tr>'
			+'<tr><th>Ad Type</th><td><select id="popup-type_id"></select></td>'
			+'<td id="image-wrapper" colspan="2" rowspan="4"></td></tr>'
			+'<tr><th>Owner</th><td><select id="popup-customer_id"></select></td></tr>'
			+'<tr><th>Active</th><td><select id="popup-is_active"><option value="1">Yes</option><option value="0">No</option></select></td></tr>'
			+'<tr><th>Expire Date</th><td><input id="popup-date_expire"/></td></tr>'
			+'<tr><th>Target Url</th></th><td colspan="3">'
			+'<input style="width:100%" id="popup-target_url"/></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'modal':true,
			'width':'600px',
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					var imgurl=$('#popup-image_url').val();
					if (imgurl.charAt(0)=='/') {
						imgurl='/f'+imgurl;
					}
					$.post('/a/p=ads/f=adminAdEdit', {
						'id':ret.id,
						'name':$('#popup-name').val(),
						'type_id':$('#popup-type_id').val(),
						'customer_id':$('#popup-customer_id').val(),
						'is_active':$('#popup-is_active').val(),
						'date_expire':$('#popup-date_expire').val(),
						'target_url':$('#popup-target_url').val(),
						'image_url':imgurl
					}, function() {
						document.location="/ww.admin/plugin.php?_plugin=ads&_page=ads";
					});
				}
			}
		});
		$('#popup-name').val(ret.name);
		$('#popup-is_active').val(ret.is_active);
		$('#popup-date_expire')
			.datepicker({
				'dateFormat':'yy-mm-dd'
			})
			.val(ret.date_expire);
		$.get('/a/f=adminUserNamesGet', function(ret2) {
			var html='<option></option>';
			$.each(ret2, function(k, v) {
				html+='<option value="'+k+'">'+v+'</option>';
			});
			$('#popup-customer_id').html(html).val(ret.customer_id);
		});
		$.get('/a/p=ads/f=adminTypesList', function(ret2) {
			var html='';
			$.each(ret2, function(k, v) {
				html+='<option value="'+k+'">'+v+'</option>';
			});
			$('#popup-type_id').html(html).val(ret.type_id);
		});
		$('#popup-target_url').val(ret.target_url);
		$('#popup-image_url')
			.val((ret.image_url||'').replace(/^\/f/, ''))
			.saorfm({
				'rpc':'/ww.incs/saorfm/rpc.php',
				'select':'file',
				'prefix':''
			})
			.change(function() {
				var url=$(this).val();
				var $wrapper=$('#image-wrapper').empty();
				if (url) {
					$wrapper.html('<img src="/a/f=getImg/w=350/h=150/'+url+'"/>');
				}
			})
			.change();
	}
	function editAdsType(ret) {
		var html='<table>'
			+'<tr><th>Name</th><td><input id="popup-name"/></td></tr>'
			+'<tr><th>Width</th><td><input id="popup-width"/></td></tr>'
			+'<tr><th>Height</th><td><input id="popup-height"/></td></tr>'
			+'<tr><th>Daily Price</th><td><input id="popup-price-per-day"/></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					$.post('/a/p=ads/f=adminTypesEdit', {
						'id':ret.id,
						'name':$('#popup-name').val(),
						'width':$('#popup-width').val(),
						'height':$('#popup-height').val(),
						'price_per_day':$('#popup-price-per-day').val()
					}, function() {
						document.location="/ww.admin/plugin.php?_plugin=ads&_page=ad-types";
					});
				}
			}
		});
		$('#popup-name').val(ret.name);
		$('#popup-height').val(ret.height);
		$('#popup-width').val(ret.width);
		$('#popup-price-per-day').val(ret.price_per_day);
	}
});
