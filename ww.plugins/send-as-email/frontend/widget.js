function sendasemail_send(tpl){
	$('<table id="sendasemail-form">'
		+'<tr><th>Your email</th><td><input type="email" name="from" /></td></tr>'
		+'<tr><th>Recipient\'s email</th><td><input type="email" name="to" /></td></tr>'
		+'<tr><th>Subject</th><td><input name="subject" /></td></tr>'
		+'</table>'
	)
		.dialog({
			modal:true,
			close:function(){
				$('#sendasemail-form').remove();
			},
			buttons:{
				'Send':function(){
					var rcp=$('#sendasemail-form input[name=to]').val();
					var sub=$('#sendasemail-form input[name=subject]').val();
					var sdr=$('#sendasemail-form input[name=from]').val();
					$.post('/ww.plugins/send-as-email/frontend/send.php',{
						url:document.location.toString(),
						tpl:tpl,
						rcp:rcp,
						sub:sub,
						sdr:sdr
					}, function(ret) {
						if(ret=='sent'){
							$('#sendasemail-form').remove();
						}
						else {
							alert(ret);
						}
					});
				}
			}
		});
}
