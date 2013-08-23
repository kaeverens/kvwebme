$(function() {
	$('.products-related-popup').each(function() {
		var $this=$(this);
		$this.click(function() {
			if ($this.data('template-body')) {
				var template_body=$('#'+$this.data('template-body')).outerHTML();
			}
			else {
				var template_body='<table style="width:100%">'
					+'<tr><th style="width:80%" data-product-val="name"/>'
					+'<td><a data-product-link="1">Read more</a></td></tr>'
					+'</table>';
			}
			if ($this.data('template-header')) {
				var template_header=$('#'+$this.data('template-header')).outerHTML();
			}
			else {
				var template_header='<h2 data-product-val="name"/>';
			}
			var pid=+$(this).closest('.products-product').attr('id')
				.replace('products-', '');
			var $popup=$('<div id="popup-wrapper">'
				+'<div id="popup-header">'+template_header+'</div>'
				+'<div id="popup-body"></div>'
				+'</div>');
			function populate($el, ret) {
				$el.css('display', 'block').removeAttr('id');
				$el.find('*').each(function() {
					var $this=$(this);
					if ($this.data('product-val')) {
						var name=$this.data('product-val');
						if (ret[name]!=undefined) {
							$this.text(ret[name]);
						}
					}
					if ($this.data('product-link')) {
						$this.attr('href', ret.link);
					}
				});
			}
			$.post(
				'/a/p=products/f=getProduct/id='+pid,
				function(ret) {
					populate($('#popup-header'), ret);
				}
			);
			$.post(
				'/a/p=products/f=getRelatedProducts/id='+pid,
				function(ret) {
					if (!ret.length) {
						return alert('no related products found');
					}
					var $body=$popup.find('#popup-body');
					for (var i=0;i<ret.length;++i) {
						var $entry=$(template_body);
						populate($entry, ret[i]);
						$body.append($entry);
					}
					$popup.dialog({
						'modal':true
					});
				}
			);
			return false;
		});
	});
});
