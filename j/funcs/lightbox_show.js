function lightbox_show(src, sequence, seq_num) {
	$('#lightbox-image').closest('table').dialog('close');
	var max_width=parseInt($(window).width()*.9),
		max_height=parseInt($(window).height()*.9);
	if (/kfmget\/[0-9]/.test(src)) {
		src=src.replace(/,.*/, '');
		src=src+',width='+max_width+',height='+max_height;
	}
	var left_arrow='',right_arrow='';
	var width_to_add=26;
	sequence=sequence.toString().split(',');
	if (sequence.length>1) {
		var lnum=+seq_num-1;
		if (lnum<0) {
			lnum=sequence.length-1;
		}
		left_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[lnum]+'\',\''+sequence+'\','+lnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-left.png"/>'
			+'</a></td>';
		var rnum=+seq_num+1;
		if (rnum>=sequence.length) {
			rnum=0;
		}
		right_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[rnum]+'\',\''+sequence+'\','+rnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-right.png"/>'
			+'</a></td>';
		width_to_add+=60;
	}
	$('object').each(function(){
		var $this=$(this);
		$this.attr('lightbox-visibility', $this.css('visibility'));
		$this.css('visibility', 'hidden');
	});
	$('<table><tr>'+left_arrow+'<td><img id="lightbox-image" src="'+src+'"/></td>'+right_arrow+'</tr></table>')
		.dialog({
			"modal":true,
			"close":function(){
				$(this).remove();
				$('object').each(function(){
					var $this=$(this);
					$this.css('visibility', $this.attr('lightbox-visibility'));
					$this.removeAttr('lightbox-visibility');
				});
			}
		});
	$('#lightbox-image').load(function(){
		var $this=$(this);
		while ($this[0].offsetWidth>max_width || $this[0].offsetHeight>max_height) {
			var r=max_width/$this[0].offsetWidth;
			var r2=max_height/$this[0].offsetHeight;
			if (r>r2) {
				r=r2;
			}
			$($this[0]).css({
				'width':$this[0].offsetWidth*r,
				'height':$this[0].offsetHeight*r
			});
		}
		$this.closest('table').dialog({
			width:$this[0].offsetWidth+width_to_add
		});
		var $dialog=$this.closest('.ui-dialog');
		$dialog.css({
			"left":$(window).width()/2-$dialog[0].offsetWidth/2,
			"top":$(window).height()/2-$dialog[0].offsetHeight/2+$(document).scrollTop()
		});
	});
}
