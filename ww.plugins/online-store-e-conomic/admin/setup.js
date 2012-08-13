$(function() {
	$('#login-to-external').click(function() {
		var agreementNumber=$('input[name=agreement_no]').val(),
			user_id=$('input[name=user_id]').val(),
			password=$('input[name=password]').val();
		var html='<form style="display:none" method="post" target="_blank" action="'
			+'https://secure.e-conomic.com/secure/internal/login.asp">'
			+'<input type="hidden" name="aftalenr" value="'+agreementNumber+'"/>'
			+'<input type="hidden" name="brugernavn" value="'+user_id+'"/>'
			+'<input type="hidden" name="password" value="'+password+'"/>'
			+'</form>';
		$(html).appendTo(document.body).submit();
		return false;
	});
});
