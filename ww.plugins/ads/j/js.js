$(function() {
	$('.ads-ad').css('cursor', 'pointer').click(function() {
		var $this=$(this);
		var id=$this.data('id');
		var type=+$this.data('type');
		if (id) {
			if (type) {
				$.post('/a/p=ads/f=track/id='+id);
				$this
					.wrap('<a href="'+$this.data('poster')+'" target="popup"/>')
					.click();
			}
			else {
				document.location='/a/p=ads/f=go/id='+id;
			}
		}
	});
});
