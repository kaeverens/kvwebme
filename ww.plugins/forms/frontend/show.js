$(function(){
	$('.ww_form').each(function(){
		var $this=$(this);
		$this.attr('current-page', 0);
		function change_page($this, from, to) {
			var $divs=$this.find('>div');
			$($divs[from])
				.animate({
					'opacity':0
				},function(){
					$(this).css('display', 'none');
					$($divs[to])
						.css({
							'opacity':0,
							'display':'block'
						})
						.animate({
							'opacity':1
						});
					$this.attr('current-page', to);
				});
		}
		function are_all_visible_elements_valid($this) {
			var $divs=$this.find('>div:visible');
			var allvalid=true;
			$divs.find('input.date').each(function() {
				var $this=$(this);
				var from=+$this.attr('year-from');
				var to=+$this.attr('year-to');
				var val=+$this.val().replace(/.*([0-9][0-9][0-9][0-9]).*/, '$1');
				if (from>val || to<val) {
					$this.attr('title', 'date out of range');
					return allvalid=false;
				}
			});
			$divs.find('input,select').each(function(){
				if ($(this).is('input.date')) {
					return allvalid;
				}
				if (!$this.validate().element(this)) {
					return allvalid=false;
				}
				if ($(this).is('input[type=email]')
					&& $(this).hasClass('verify')
					&& !$(this).hasClass('verified')
				) {
					allvalid=false;
				}
			});
			return allvalid;
		}
		$this.find('.form-page-next').click(function(){
			if (!are_all_visible_elements_valid($this)) {
				return;
			}
			var curpage=+$this.attr('current-page');
			change_page($this, curpage, curpage+1);
		});
		$this.find('.form-page-previous').click(function(){
			if (!are_all_visible_elements_valid($this)) {
				return;
			}
			var curpage=+$this.attr('current-page');
			change_page($this, curpage, curpage-1);
		});
		$this.find('textarea').each(function(){
			var $this=$(this);
			var maxlength=+$this.attr('maxlength');
			var softmaxlength=+$this.attr('softmaxlength');
			if (maxlength) {
				$this.keyup(function(){
					if ($this.val().length>maxlength) {
						$this.val($this.val().substring(0,maxlength));
					}
				});
			}
			if (softmaxlength) {
				$this.keyup(function(){
					if ($this.val().length>softmaxlength) {
						$this.addClass('warning');
					}
					else {
						$this.removeClass('warning');
					}
				});
			}
		});
		$this.find('input[type=email].verify').change(function(){
			var $this=$(this);
			var name=$this.attr('name'),email=$this.val();
			$.post('/a/p=forms/f=emailVerify', {
				"email": $this.val(),
				"page" : pagedata.id
			}, function(ret) {
				if (ret.ok) {
					$this.addClass('verified');
					$('input[name='+name+'_verify]')
						.css('display','none');
				}
				else {
					$this.removeClass('verified');
					$('input[name='+name+'_verify]')
						.css('display','block');
					$.post('/a/p=forms/f=verificationSend', {
						"email": email,
						"page" : pagedata.id
					}, function(ret) {
						if (ret.error) {
							return alert(ret.error);
						}
						alert('please check your email for a verification code, and fill it in');
					});
				}
			});
		});
		$this.find('input[type=email].verify').each(function() {
			if ($(this).val()!='') {
				$(this).change();
			}
		});
	});
	$('.download-delete-item').click(function(){
		var $this=$(this);
		var id=$this.attr('id');
		$.post('/a/p=forms/f=fileDelete/id='+id);
		$this.parent().parent().fadeOut('fast');
	});
	$("input.date").each(function(){
		var $this=$(this);
		var range=$this.attr('metadata').split(',');
		if (range.length != 2) {
			range=[1900,2100];
		}
		$this.attr('year-from', range[0]);
		$this.attr('year-to', range[1]);
		$this.datepicker({
			"dateFormat":"yy-mm-dd",
			"changeMonth":true,
			"changeYear":true,
			"yearRange":range[0]+':'+range[1]
		});
	});
	if (forms_helpType) {
		$('.ww_form').find('input,select,textarea').hover(function() {
			$(forms_helpSelector).html(this.title);
		});
	}
	else {
		$('.ww_form').find('input,select,textarea').tooltip();
	}
	$('.email-verification').keyup(function() {
		var $this=$(this);
		var $email=$this.siblings('input');
		$.post('/a/p=forms/f=emailVerify', {
			"email":$email.val(),
			"page":pagedata.id,
			"code":$this.val()
		}, function(ret) {
			if (!ret.error) {
				$email.change();
			}
		});
	});
});
