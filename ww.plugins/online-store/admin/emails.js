$(function() {
	$('select[name="onlinestore-emails-type"]').change(function() {
		$('input[name="action"]').val('none');
		$(this).closest('form').submit();
	});
});
