$(function() {
	$('.ads-ad').css('cursor', 'pointer').click(function() {
		var id=$(this).data('id');
		if (id) {
			document.location='/a/p=ads/f=go/id='+id;
		}
	});
});
