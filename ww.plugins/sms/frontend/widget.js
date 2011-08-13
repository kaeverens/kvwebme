function sms_get_phone_error(val){
	if (val.replace(/[^0-9]/,'')!=val) {
		return 1; // numbers only
	}
	if (!val.test(/^44|^353/)) {
		return 2; // only Ireland and UK numbers currently allowed
	}
	return 0;
}
$('input.sms-phone').live('change',function(){
	switch(sms_get_phone_error($(this).val())){
		case 1:
			return alert('please use numbers only in the phone field.');
		case 2:
			return alert('number must begin with the country code.\nalso, drop the leading 0 of the network number.\nexample: "087 1234567" becomes "353871234567"');
	}
});
$('.sms-subscribe button').live('click',function(){
	var $container=$(this).closest('div.sms-subscribe');
	var ids=[];
	$container.find('input:checked').each(function(){
		ids.push($(this).val());
	});
	var name=$container.find('.sms-name').val();
	var phone=$container.find('.sms-phone').val();
	if (!name) {
		return alert('Name must not be empty');
	}
	if (sms_get_phone_error(phone)) {
		return alert('please check the Phone field.');
	}
	$.post('/a/p=sms/f=subscribe', {
		"ids":ids.join(','),
		"name":name,
		"phone":phone
	},function(res){
		if(res.err){
			alert(res.errmsg);
		}
		else {
			alert('Thank you - you have been subscribed.');
		}
	},'json');
});
