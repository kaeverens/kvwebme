$(function() {
	function vote() {
		var $wrapper=$(this).closest('.polls-wrapper');
		var val=+$wrapper.find('input:radio:checked').val();
		if (!val) {
			return alert('select an answer first!');
		}
		$.post('/ww.plugins/polls/vote.php', {
			'id':$wrapper.attr('poll-id'),
			'vote':val
		}, function(ret) {
			if (ret.status) {
				alert(ret.message);
			}
			setTimeout(showResults, 1000);
		});
	}
	function showResults() {
		var $wrapper=$(this).closest('.polls-wrapper');
		$.post('/ww.plugins/polls/results.php', {
			'id':$wrapper.attr('poll-id')
		}, function(ret) {
			if (ret.status) {
				return alert(ret.message);
			}
			$wrapper.animate({
				'opacity':0
			}, function() {
				$wrapper.html(ret.html).animate({
					'opacity':1
				});
			});
		});
	}
	$('.polls-wrapper').each(function() {
		var $this=$(this);
		$this.find('.polls-vote').click(vote);
		$this.find('.polls-results').click(showResults);
	});
});
