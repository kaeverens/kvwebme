$(function() {
	$('.product-variants-popup').each(function() {
		var $this=$(this);
		var $sel=$(this);
		$sel.css('display', 'none');
		var html='<button/>';
		var tplId=$sel.data('variants-template');
		if (tplId) {
			$('#'+tplId).css('display', 'none');
		}
		var $this=$(html)
			.insertAfter($sel).text($sel.find(':selected').text()+' ▾')
			.data('template-body', tplId);
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
			alert(template_body);
			var pid=+$(this).closest('.products-product').attr('id')
				.replace('products-', '');
			var $popup=$('<div id="popup-wrapper">'
				+'<div id="popup-body"></div>'
				+'</div>');
			function populate($el, ret) {
				$el.css('display', 'block').removeAttr('id');
				$el.find('*').each(function() {
					var $this=$(this);
					if ($this.data('product-val')) {
						var name=$this.data('product-val');
						console.log(name);
						if (ret[name]!=undefined) {
							$this.text(ret[name]);
						}
					}
					if ($this.data('product-link')) {
						$this.attr('href', ret.link);
					}
				});
			}
			return false;
		});
	});
});
