function sms_edit(id){
	if(id){
		$.post('/a/p=sms/f=adminSubscribersGet/id='+id, sms_edit_showform, 'json');
	}
	else {
		sms_edit_showform();
	}
}
function sms_edit_showform(res){
	if(!res){
		res={
			"id":0,
			"name":'[insert subscriber name]',
			"phone":''
		};
	}
	$('#sms_wrapper').html('<table>'
		+'<tr><th>Name</th><td><input id="sms_name" /></td></tr>'
		+'<tr><th>Phone</th><td><input id="sms_phone" /></td></tr>'
		+'<tr><th colspan="2"><button>Save</button></th></tr>'
		+'</table>'
	);
	$('#sms_name').val(res.name);
	$('#sms_phone').val(res.phone);
	$('#sms_wrapper button').click(sms_save);
	window.sms_currently_editing=res;
}
function sms_save(){
	var res=window.sms_currently_editing;
	res.name=$('#sms_name').val();
	res.phone=$('#sms_phone').val();
	if(!res.phone || !res.name)return alert('please provide a name and phone number');
	if(res.phone.replace(/[0-9]*/,'')!='' || /^0/.test(res.phone))return alert('please only use numbers in the phone number\nnumber should be of the format "353871234567"\n(country [00]353 + network [0]87 + number 1234567)');
	if(!/^44|^353/.test(res.phone))return alert('only UK (44) and Irish (353) numbers are accepted at present.');
	$.post('/a/p=sms/f=adminSubscribersSave/id='+res.id+'/name='+res.name
		+'/phone='+res.phone, function(res){
			if (res.err) {
				alert('error saving subscriber\nplease check your values');
			}
			else {
				document.location="/ww.admin/plugin.php?_plugin=sms&_page=subscribers";
			}
		},
		'json'
	);
}
function sms_delete(id){
	if(!confirm('are you sure you want to delete this subscriber?'))return;
	$.post('/a/p=sms/f=adminSubscribersDelete/id='+id, function(){
		document.location='/ww.admin/plugin.php?_plugin=sms&_page=subscribers';
	});
}
