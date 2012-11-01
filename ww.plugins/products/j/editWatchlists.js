window.Products_editWatchlists=function() {
	if (Products_editWatchlists.categories==undefined) {
		$.post('/a/p=products/f=categoriesOptionsGet', function(ret) {
			Products_editWatchlists.categories=ret;
			Products_editWatchlists();
		});
		return;
	}
	if (Products_editWatchlists.locations==undefined) {
		$.post('/a/f=locationsGetFull', function(ret) {
			Products_editWatchlists.locations=ret;
			Products_editWatchlists();
		});
		return;
	}
	$.post('/a/p=products/f=watchlistsGet', function(ret) {
		var html='<div>'
			+'<p>'+__('This lists the categories that you are currently watching.')
			+'</p>'
			+'<table style="width:100%">'
			+'<thead><tr><th>Category</th><th>Located</th></tr></thead><tbody>';
		var cats=Products_editWatchlists.categories,
			locs=Products_editWatchlists.locations;
		for (var i=0;i<ret.length;++i) {
			html+='<tr><td><select class="category"><option></option>';
			$.each(cats, function(k, v) {
				html+='<option value="'+k+'"';
				if (k==ret[i].category_id) {
					html+=' selected="selected"';
				}
				html+='>'+v+'</option>';
			});
			html+='</select></td>';
			html+='<td><select class="location"><option value="">Anywhere</option>';
			$.each(locs, function(k, v) {
				html+='<option value="'+v+'"';
				if (v==ret[i].location_id) {
					html+=' selected="selected"';
				}
				html+='>'+k+'</option>';
			});
			html+='</select></td></tr>';
		}
		html+='</tbody></table></div>';
		$dialog=$(html);
		if (window.ProductsWatchlistTemplate) { // apply custom template to html
			ProductsWatchlistTemplate($dialog);
		}
		var $dialog=$($dialog).dialog({
			'modal':true,
			'position':['center', 100],
			'width':'600px',
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					var watchlists=[];
					var $rows=$('tbody>tr', $dialog);
					$rows.each(function() {
						var $sels=$('select', this);
						if (!$sels.val()) {
							return;
						}
						watchlists.push({
							'category_id':$sels.val(),
							'location_id':$($sels[1]).val()
						});
					});
					$.post('/a/p=products/f=watchlistsSave', {
						'watchlists': watchlists
					}, function() {
						$dialog.remove();
					});
				}
			}
		});
		function checkRows() {
			var $rows=$('tbody>tr', $dialog);
			var blank=0;
			$rows.each(function() {
				var val=$('select', this).val();
				if (val=='') {
					blank++;
				}
				if (blank>1) {
					$(this).remove();
				}
			});
			if (!blank) {
				var html='<tr><td><select class="category"><option></option>';
				$.each(cats, function(k, v) {
					html+='<option value="'+k+'">'+v+'</option>';
				});
				html+='</select></td>';
				html+='<td><select class="location"><option value="">Anywhere</option>';
				$.each(locs, function(k, v) {
					html+='<option value="'+v+'">'+k+'</option>';
				});
				html+='</select></td></tr>';
				$(html).appendTo($dialog.find('table'));
			}
		}
		checkRows();
		$dialog.find('table').on('change', 'select', checkRows);
	});
}
