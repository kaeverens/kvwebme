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
			$divs.find('input,select').each(function(){
				if (!$this.validate().element(this)) {
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
			var name=$this.attr('name');
			$.post('/ww.plugins/forms/frontend/send-verification.php',{
				'name':name,
				'email':$this.val()
			}, function(ret) {
				if (ret.error) {
					return alert(ret.error);
				}
				$this.siblings('input').css('display', 'block');
				alert('please check your email for a verification code, and fill it in');
			});
		});
		$this.find('input[type=email].verify').each(function() {
			if ($(this).val()!='') {
				$(this).change();
			}
		});
	})
		.validate(window.form_rules);
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
		$this.datepicker({
			"dateFormat":"yy-mm-dd",
			"changeMonth":true,
			"changeYear":true,
			"yearRange":range[0]+':'+range[1]
		});
	});
});
