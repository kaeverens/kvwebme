$(function(){
	$('.ww_form').each(function(){
		var $this=$(this);
		$this.attr('current-page', 0);
		function change_page($this, from, to) {
			var $divs=$this.find('>fieldset>div');
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
		$this.find('.form-page-next').click(function(){
			var curpage=+$this.attr('current-page');
			change_page($this, curpage, curpage+1);
		});
		$this.find('.form-page-previous').click(function(){
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
				$this.next().css('display', 'block');
				alert('please check your email for a verification code, and fill it in');
			});
		});
	});
});
