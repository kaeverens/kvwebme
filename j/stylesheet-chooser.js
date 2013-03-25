(function() {
	function setAlternateCss(title) {
		if (title=='') {
			return;
		}
		$('#stylesheet-chooser a').removeClass('enabled');
		$('#stylesheet-chooser a[data-sheet="'+title+'"]').addClass('enabled');
		var $sheets=$('link[type="text/css"]');
		$sheets.each(function(k, v) {
			var thisName=$(this).attr('title');
			if (thisName==title) {
				this.disabled=false;
				$(this).attr('rel', 'stylesheet');
				console.log('found', title);
			}
			else {
				this.disabled=true;
				console.log('not found', title, thisName);
			}
		});
	}
	$.post('/a/f=alternateCssGet', setAlternateCss);
	$(function() {
		$('#stylesheet-chooser a').click(function() {
			var title=$(this).data('sheet');
			console.log('clicked');
			$.post('/a/f=alternateCssSet', {
				'css':title
			}, setAlternateCss);
		});
	});
})();
