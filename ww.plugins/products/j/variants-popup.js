$(function() {
	$('.product-variants-popup').each(function() {
		var $sel=$(this);
		var $productEl=$sel.closest('.products-product');
		$sel.css('display', 'none');
		var html='<button class="products-variant-selector"/>';
		var tplBody=$sel.data('variants-template'),
			tplHeader=$sel.data('variants-template-header');
		if (tplBody) {
			$('#'+tplBody).css('display', 'none');
		}
		if (tplHeader) {
			$('#'+tplHeader).css('display', 'none');
		}
		var $this=$(html)
			.insertAfter($sel).text($sel.find(':selected').text()+' ▾')
			.data('template-body', tplBody)
			.data('template-header', tplHeader);
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
			var template_header='<h2 data-product-val="name"/>';
			if ($this.data('template-header')) {
				template_header=$('#'+$this.data('template-header')).outerHTML();
			}
			var pid=+$(this).closest('.products-product').attr('id')
				.replace('products-', '');
			var $popup=$('<div id="popup-wrapper">'
				+'<div id="popup-header"></div>'
				+'<div id="popup-body"></div>'
				+'</div>');
			function populate($el, ret) {
				$el.show().removeAttr('id');
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
					ret._base_price=+ret._price;
					var $tplHeader=$(template_header);
					var $head=$popup.find('#popup-header');
					$head.append($tplHeader);
					populate($head.find('>*'), ret);
					var $body=$popup.find('#popup-body');
					var addToBasePrice=0;
					var thisName=$sel.attr('name').replace('products_values_', '');
					$productEl.find('input,select').each(function() {
						var $this=$(this);
						var name=$this.attr('name');
						if (name==thisName || !(/^products_values_/.test(name))
							|| $this.is('form input, form select')
						) {
							return;
						}
						var val=$this.val();
						if ($this.is('select') && /\|[0-9]/.test(val)) {
							addToBasePrice+=+val.replace(/.*\|/, '');
							val=val.replace(/\|.*/, '');
						}
						ret[name.replace('products_values_', '')]=val;
					});
					$sel.find('option').each(function() {
						var addToBasePrice2=0;
						var $entry=$(template_body);
						var val=$(this).attr('value');
						// { "Add to Cart" button
						var $select=$('<button>'+__('Add to Cart')+'</button>')
							.click((function(variant, val){
								return function() {
									$sel.val(val);
									$this.text($sel.find(':selected').text()+' ▾');
									$popup.remove();
									$sel.change();
									setTimeout(function() {
										$productEl.find('input[type=submit],button.submit-button')
											.trigger('mouseover')
											.trigger('click');
									}, 1);
									return false;
								}
							})(thisName, val));
						$entry.find('.products-add-to-cart').append($select);
						// }
						// { "Select Variant" button
						var $select=$('<button>'+__('Select Variant')+'</button>')
							.click((function(variant, val){
								return function() {
									$sel.val(val).change();
									$this.text($sel.find(':selected').text()+' ▾');
									return $popup.remove();
								}
							})(thisName, val));
						$entry.find('.products-select-variant').append($select);
						// }
						if (/\|[0-9]/.test(val)) {
							addToBasePrice2+=+val.replace(/.*\|/, '');
							val=val.replace(/\|.*/, '');
						}
						ret[thisName]=val;
						if (ret._sale_price && ret._sale_price<ret._base_price) {
							ret._price=+ret._sale_price+addToBasePrice2;
						}
						else {
							ret._price=ret._base_price+addToBasePrice2;
						}
						ret._amt_in_stock=0;
						if (ret.stockcontrol && ret.stockcontrol.length) {
							for (var j=0;j<ret.stockcontrol.length;++j) {
								var found=true;
								$.each(ret.stockcontrol[j], function(k, v) {
									if (k=='_amt') {
										return;
									}
									if (ret[k]!=v) {
										found=false;
									}
								});
								if (found) {
									ret._amt_in_stock=ret.stockcontrol[j]._amt;
								}
							}
						}
						populate($entry, ret);
						$body.append($entry);
					});
					$popup.dialog({
						'modal':true,
						'width':'80%'
					});
					$('.ui-dialog').addClass('products-variants-popup');
				}
			);
			return false;
		});
	});
});
