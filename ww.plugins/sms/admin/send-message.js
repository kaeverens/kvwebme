function sms_change_type(){
	var val=$('#sms_send_type').val();
	if(val=='Phone Number'){
		$('#sms_addressbook_id').css('display','none');
		$('#sms_single').css('display','block');
	}
	else{
		$('#sms_addressbook_id').css('display','block');
		$('#sms_single').css('display','none');
	}
}
function sms_check_msg(){
	var msg=$('#sms_msg').val();
	var newmsg=msg.replace(/[^\[\]a-zA-Z0-9 !_\-.,:'"]*/g,'');
	if(newmsg.length>160)newmsg=newmsg.substring(0,160);
	if(msg!=newmsg)$('#sms_msg').val(newmsg);
}
function sms_check_to(){
	var to=$('#sms_to').val();
	var newto=to.replace(/[^0-9]*/g,'');
	if(to!=newto)$('#sms_to').val(newto);
}
function sms_choose_from_subscribers(id){
	if(id) {
		$('/a/p=sms/f=adminSubscribersGet/id='+id, function(res){
			if(res){
				$('#sms_to').val(res.phone);
				$('#sms_to_name').val(res.name);
			}
			$('#sms-choose-from-addressbook').remove();
		},'json');
	}
	else {
		$.post('/a/p=sms/f=adminSubscribersGet', function(res){
			var links=[];
			for(var i=0;i<res.length;++i){
				links.push('<a href="javascript:sms_choose_from_subscribers('+res[i].id+');">'
					+htmlspecialchars(res[i].name)
					+'</a>');
			}
			$('<div id="sms-choose-from-addressbook"><p>Click a name.</p>'+links.join(', ')+'</div>')
				.dialog({
					"modal":true
				});
		},'json');
	}
}
function sms_send(){
	sms_check_msg();
	var msg=$('#sms_msg').val();
	if(msg=='')return alert('no message!');
	if($('#sms_send_type').val()=='Phone Number'){
		sms_check_to();
		var to=$('#sms_to').val();
		if(to.replace(/[0-9]*/,'')!='' || /^0/.test(to))return alert('please only use numbers in the phone number\nnumber should be of the format "353871234567"\n(country [00]353 + network [0]87 + number 1234567)');
		if(!/^44|^353/.test(to))return alert('only UK (44) and Irish (353) numbers are accepted at present.');
		var name=$('#sms_to_name').val();
		if(name=="name (optional)")name=to;
		$.post('/a/p=sms/f=adminSend', {
			"to":to,
			"to_name":$('#sms_to_name').val(),
			"msg":msg
		}, sms_sent, 'json');
	}
	else{
		var aid=$('#sms_addressbook_id').val();
		if(aid==0)return alert('please choose an addressbook');
		$.post('/a/p=sms/f=adminSendBulk', {
			"to":aid,
			"msg":msg
		}, sms_sent_bulk, 'json');
	}
}
function sms_sent(ret){
	var msg='';
	if(!ret.status){
		msg='<p><i>'+ret.error+'</i></p>';
	}
	else{
		msg='<p>sms sent to '+$('#sms_to').val()+'</p>';
	}
	$('#sms_log').append(msg);
}
function sms_sent_bulk(ret){
	var msg='';
	if(!ret.status){
		msg='<p><i>'+ret.error+'</i></p>';
	}
	else{
		msg='<p>smses sent to addressbook #'+$('#sms_addressbook_id').val()+'</p>';
	}
	$('#sms_log').append(msg);
}
$(function(){
	$('#sms-send-table button').click(sms_send);
	$('#sms_to')
		.blur(function(){
			if($(this).val()=='')$(this).val('phone');
		})
		.focus(function(){
			if($(this).val()=='phone')$(this).val('');
		})
		.keyup(sms_check_to);
	$('#sms_to_name')
		.blur(function(){
			if($(this).val()=='')$(this).val('name (optional)');
		})
		.focus(function(){
			if($(this).val()=='name (optional)')$(this).val('');
		})
	$('#sms_msg').keyup(sms_check_msg);
	$('#sms_send_type').change(sms_change_type);
});
