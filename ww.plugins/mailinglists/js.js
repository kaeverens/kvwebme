$(function() {
	$('#mailinglists-subscribe button').click(function() {
		var email=$('#mailinglists-subscribe input').val();
		var list=$('#mailinglists-subscribe select');
		list=list.length?list.val():0;
		$.post(
			'/a/p=mailinglists/f=subscribe/list='+list,
			{'email':email},
			function(ret) {
				if (ret.error) {
					return alert(ret.error);
				}
				$('#mailinglists-subscribe input').val('');
				return alert(
					'Thank you. Please check your email to verify the subscription'
				);
			}
		);
	});
});
