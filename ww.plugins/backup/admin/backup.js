$('input[name=action]').mousedown(function(){
	if (!$('input[name=password]').val()) {
		alert('password is required');
		return false;
	}
	if (this.disabled=='disabled') {
		return;
	}
	this.disabled='disabled';
	$(this).closest('form').submit();
});
