$(function(){
	$('#onlinestore-vouchers').dataTable({
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": "/ww.plugins/online-store/admin/vouchers-ajax.php"
	});
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
			var table='<table><tr><th>Users</th><th>Email addresses</th>'
				+'<td rowspan="2" style="width:100px">Select which users are allowed to use this '
				+'voucher, and/or add a list of email addresses of people that'
				+' are allowed to use this voucher.</td></tr>'
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
