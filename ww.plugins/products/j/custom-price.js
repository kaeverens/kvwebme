$(function() {
	$('.products-custom-price').each(function() {
		var $this=$(this);
		$this
			.attr('placeholder', '0.00')
			.css('max-width', '100px')
			.keyup(function() {
				if ($this.val().replace(/[0-9.]/g, '')!='') {
					$this.val($this.val().replace(/[^0-9.]/g, ''));
				}
			});
	});
});
