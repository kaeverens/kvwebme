$(function() {
	$.getScript('/j/featuredimagezoomer-1.51/featuredimagezoomer.js', function() {
		$('.products-image .zoom').load(function() {
			var src=this.src.replace(/.*\/\//, '/f/');
			src=src.replace(/\/w=.*/, '');
			var pos=this.className.replace(/.*zoom-pos-([^ $]*).*/, '$1');
			$(this).addimagezoom({
				zoomrange:[3, 3],
				magnifiersize:[300, 300],
				magnifierpos:pos,
				cursorshade:true,
				largeimage:src
			});
		});
	});
});
