$('#panels').on(
	'change',
	'.panel-facebook select[name=what_to_show]',
	function() {
		var $this=$(this);
		var what_to_show=$this.val();
		var $p=$this.closest('div');
		$p.find('>div').css('display', 'none');
		$p.find('>div.'+what_to_show).css('display', 'block');
	});
