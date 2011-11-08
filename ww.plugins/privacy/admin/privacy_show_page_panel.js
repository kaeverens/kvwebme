$('#privacy-options input').live('change', function() {
	var $this=$(this);
	if ($('#pages-common select[name=type]').val()!='privacy|privacy') {
		return;
	}
	if ($this.is('input[type=checkbox]')) {
		if ($this.is(':checked')) {
			alert(
				'If you protect this page from public view, then people may not '
				+'be able to log in or register'
			);
		}
		return;
	}
	if ($this.val()) {
		alert(
			'If you protect this page from public view, then people may not '
			+'be able to log in or register'
		);
	}
});
