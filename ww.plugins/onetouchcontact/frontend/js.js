function onetouchcontact_setupinput($form,id,name){
	$form.find('#onetouchcontact-'+id)
		.val('Your '+name+' Here')
		.focus(function(){
			if(this.value=='Your '+name+' Here')this.value='';
		})
		.blur(function(){
			if(this.value=='')this.value='Your '+name+' Here';
		});
}
$(function(){
	var $form=$('form.onetouchcontact');
	onetouchcontact_setupinput($form,'email','Email');
	onetouchcontact_setupinput($form,'name','Name');
	onetouchcontact_setupinput($form,'phone','Phone');
	$form.bind('submit',function(){
		$.post('/ww.plugins/onetouchcontact/frontend/subscribe.php',{
				cid:$form.find('input[name=cid]').val(),
				mid:$form.find('input[name=mid]').val(),
				email:$form.find('#onetouchcontact-email').val(),
				name:$form.find('#onetouchcontact-name').val(),
				phone:$form.find('#onetouchcontact-phone').val()
			},
			function(ret){
				if(ret!='0'){
					if(ret=='2')ret='<div class="errors">you\'ve already subscribed.</div>';
					return $form.find('.onetouchcontact-msg').html(ret);
				}
				onetouchcontact_setupinput($form,'email','Email');
				onetouchcontact_setupinput($form,'name','Name');
				onetouchcontact_setupinput($form,'phone','Phone');
				return $form.find('.onetouchcontact-msg').html('<strong>thank you</strong>');
			}
		);
		return false;
	});
});
