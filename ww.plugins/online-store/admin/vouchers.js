$(function(){
	var params={
		"bProcessing": true,
		"bServerSide": true,
		"bJQueryUI": true,
		"sAjaxSource": "/ww.plugins/online-store/admin/vouchers-ajax.php"
	};
	if (jsvars.datatables['onlinestore-vouchers']) {
		params["iDisplayLength"]=jsvars.datatables['onlinestore-vouchers'].show;
	}
	$('#onlinestore-vouchers').dataTable(params);
	// { users_list
	var $user_constraints=$('select[name=user_constraints]');
	$user_constraints.change(build_users_list);
	function build_users_list() {
		var $wrapper=$('#onlinestore-vouchers-users-list>td');
		if ($user_constraints.val()=='userlist') {
			var users=$.parseJSON($wrapper.attr('userslist'));
			if (!users) {
				users={
					'users':[],
					'emails':[]
				};
			}
			var user_checkboxes='';
			var table='<table><tr><th>'+__('Users')+'</th><th>'+__('Email addresses')+'</th>'
				+'<td rowspan="2" style="width:100px">'+__('Select which users are allowed to use this '
				+'voucher, and/or add a list of email addresses of people that'
				+' are allowed to use this voucher')+'</td></tr>'
				+'<tr><td><ul id="os-vouchers-users">'+user_checkboxes+'</ul></td>'
				+'<td id="os-vouchers-emails"><textarea style="width:400px" name="user_emails">'
				+users.emails.join("\n")+'</textarea></td></tr></table>';
			$wrapper.html(table);
			$.get('/ww.plugins/online-store/admin/vouchers-get-users.php?users='+users.users, function(ret) {
				$('#os-vouchers-users').html(ret);
			});
		}
		else {
			$wrapper.empty();
		}
	}
	build_users_list();
	// }
});
