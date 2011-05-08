$(function(){
	$('input[name=os_voucher]').change(function() {
		var $this=$(this);
		var code=$this.val();
		if (!code) {
			return;
		}
		var email=$('#ww-pagecontent input[name=Email]').val();
		$.post('/ww.plugins/online-store/frontend/voucher-check.php', {
			"email": email,
			"code" : code
		}, function(ret) {
			if (ret.error) {
				$('<em>'+ret.error+'</em>').dialog({
					"modal": true
				});
				$this.val('');
				return;
			}
			$('<input type="hidden" name="os_no_submit" value="1"/>')
				.insertAfter($this);
			$this.closest('form').submit();
		}, 'json');
	});
	if (os_post_vars) {
		for (var i in os_post_vars) {
			$('input[name="'+i+'"],select[name="'+i+'"],textarea[name="'+i+'"]')
				.val(os_post_vars[i]);
		}
	}
});
