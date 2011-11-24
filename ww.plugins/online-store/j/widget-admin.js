$('.panel-online-store input[name=slidedown]').live('change', function() {
	var $this=$(this);
	if ($this.is(':checked')) {
		$('#online-store-slide').show();
	}
	else {
		$('#online-store-slide').hide();
	}
});
