$(function(){
	$('#events_wrapper').css('width:100%');
	var timer;
	function onchange(year, month, inst) {
		clearTimeout(timer);
		timer=setTimeout(function(){
			$.post('/ww.plugins/news/frontend/get-headlines-month.php',{
				y:year,
				m:month,
				p:pagedata.id
			},function(ret){
				var $list=$('#events_list').empty();
				var html='';
				for (var i=0;i<ret.length;++i) {
					var h=ret[i];
					html+='<strong>'+date_m2h(h.adate)+'</strong>'
						+'<p><a href="'+h.url+'">'+h.headline+'</a></p>';
				}
				$list.html(html);
			},'json');
		},100);
	}
	var $cal=$('#events_calendar')
		.datepicker({
			changeMonth: true,
			changeYear: true,
			onChangeMonthYear: onchange
		});
	$cal.datepicker('setDate','01/01/2001');
	$cal.datepicker('setDate'); // two lines, as we want the onChangeMonthYear to trigger
	$cal.closest('tr').css('width:30%');

});
