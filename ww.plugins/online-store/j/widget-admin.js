$(function() {
	$('.panel-online-store').on('change', 'input[name=slidedown]', function() {
		var $this=$(this);
		if ($this.is(':checked')) {
			$('#online-store-slide').show();
		}
		else {
			$('#online-store-slide').hide();
		}
	});
});
