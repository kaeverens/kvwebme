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
	});
});
