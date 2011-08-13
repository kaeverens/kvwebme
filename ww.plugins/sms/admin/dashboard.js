function sms_show_paypal_button(res){
	if(res.status)$('#sms_paypal_button_holder').html(res.message);
	else $('#sms_paypal_button_holder').html(res.error);
}
function sms_set_sender_id(){
//	var new_senderid=
}
$(function() {
	$('#sms_purchase_amt').change(function() {
		var amt=+$('#sms_purchase_amt').val();
		if (!amt) {
			return $('#sms_paypal_button_holder').empty();
		}
		$('#sms_paypal_button_holder').html('<p>please wait...</p>');
		$.post('/a/p=sms/f=adminButtonPaypalGet/amt='+amt, sms_show_paypal_button);
	});
	$('#sms_senderid').click(sms_set_sender_id);
});
