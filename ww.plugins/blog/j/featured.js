$(function() {
	var $articles=$('#blog-featured-excerpts .main .featured-excerpt');
	var articles=[];
	var html='<ul>';
	$articles.each(function() {
		var $this=$(this);
		var title=$this.find('.blog-header').text();
		var image=$this.find('.blog-excerpt-image').attr('src');
		var $link=$this.find('.blog-link-to-article');
		$this.data('href', $link.attr('href'))
			.css('cursor', 'pointer')
			.click(function() {
				document.location=$(this).data('href');
			});
		html+='<li>';
		if (image) {
			html+='<img src="'+image.replace(/\/w=[0-9]*\/h=[0-9]*/, '/w=160/h=100')
				+'"/>';
		}
		html+='<span>'+title+'</span>';
	});
	html+='</ul>';
	var $carousel=$('#blog-featured-excerpts .carousel');
	$(html).appendTo($carousel);
	$('<div data-dir="1" class="nav-right">&gt;</div>')
		.appendTo($carousel);
	$('<div data-dir="-1" class="nav-left">&lt;</div>')
		.appendTo($carousel);
	$('#blog-featured-excerpts .carousel .nav-left, #blog-featured-excerpts .carousel .nav-right')
		.css('cursor', 'pointer')
		.click(function() {
			var $ul=$carousel.find('>ul');
			$ul.position({
				'offset':(-160*$(this).data('dir'))+' 0',
				'of':$ul
			});
		});
	$carousel.find('li')
		.css('cursor', 'pointer')
		.click(function() {
			var i=$(this).index('#blog-featured-excerpts .carousel li');
			$('#blog-featured-excerpts .main .featured-excerpt')
				.animate({
					'opacity':0,
				}, 'fast', function() {
					$('#blog-featured-excerpts .main .featured-excerpt')
						.css('display', 'none');
					$('#blog-featured-excerpts .main .featured-excerpt:nth-child('+(i+1)+')')
						.css('display', 'block')
						.animate({
							'opacity':1
						});
				});
		});
	var active=0;
});
