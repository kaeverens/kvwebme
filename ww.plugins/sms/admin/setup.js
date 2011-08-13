function sms_verify_setup(){
	var email=$('#sms_email').val();
	var pass=$('#sms_password').val();
	var pass2=$('#sms_password2').val();
	var phone = $('#sms_phone_number').val();
	if(!email)return sms_show_error('please enter the email address');
	if(!pass)return sms_show_error('please enter a password');
	if(pass!=pass2)return sms_show_error('the entered passwords must be the same');
	$('#sms_messages').html('submitting form... please wait');
	$.post('/a/p=sms/f=adminSubscribe', {
		'email':email,
		'pass':pass,
		'phone':phone
	}, sms_subscription_sent, 'json');
}
function sms_show_error(err){
	$('#sms_messages').html('<em>'+err+'</em>');
}
function sms_subscription_sent(res){
	if(!res.status)return sms_show_error(res.error);
	$('#sms_account_setup input').closest('tr').remove();
	$('#sms_messages').html('<p>Registration successful. Please check your email mailbox for an email from textr.mobi. Copy and paste the 32-character activation key into the input box below then press Enter.</p><p>You do not need to click the link in the email.</p><input id="sms_text_activation" /></p>');
	$('#sms_text_activation').change(sms_subscription_activation);
}
function sms_subscription_activation(){
	var val=$('#sms_text_activation').val();
	if(val=='')return;
	if(val.length!=32)return alert('the activation key must be 32 characters long');
	$.post('/a/p=sms/f=adminActivate/key='+val, sms_subscription_activation2,
		'json');
}
function sms_subscription_activation2(res){
	if(res.status==0)return alert(res.error);
	$('#sms_messages').html('<p>Activation completed. You can purchase credits now. <a href="/ww.admin/plugin.php?_plugin=sms&_page=dashboard">Click here to continue</a>.</p>');
}

$(function(){
	$('#sms_account_setup input').change(sms_verify_setup);
});
