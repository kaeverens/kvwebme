$(function() {
	$as=$('.product-categories a');
	$as.each(function() {
		var cid=+$(this).data('cid');
		if (!cid) {
			return;
		}
		$('<span class="product-categories-watch product-categories-watch-'+cid+'"'
			+' title="click this to receive notification when a new product is added to this category" data-cid="'+cid+'"/>')
			.click(function() {
				var watched=$(this).hasClass('watched'), cid=$(this).data('cid');
				if (watched) {
					$(this).removeClass('watched');
					$.post('/a/p=products/f=categoryUnwatch', {
						'cid': cid
					});
				}
				else {
					$(this).addClass('watched');
					$.post('/a/p=products/f=categoryWatch', {
						'cid': cid
					});
				}
			})
			.insertBefore(this);
	});
	$.post('/a/p=products/f=categoryWatches', function(ret) {
		for (var i=0;i<ret.length;++i) {
			$('.product-categories-watch-'+ret[i]).addClass('watched');
		}
	});
});
