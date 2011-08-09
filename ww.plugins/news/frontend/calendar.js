$(function(){
	$('#events_wrapper').css('width:100%');
	var timer;
	var highlights=[];
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
				highlights['d'+year+'|'+month]=[];
				for (var i=0;i<ret.length;++i) {
					var h=ret[i];
					var day=+h.adate.replace(/.*-/, '');
					if (!highlights['d'+year+'|'+month][day]) {
						highlights['d'+year+'|'+month][day]=[];
					}
					highlights['d'+year+'|'+month][day].push(h.headline);
					html+='<strong>'+date_m2h(h.adate)+'</strong>'
						+'<p><a href="'+h.url+'">'+h.headline+'</a></p>';
				}
				$list.html(html);
				$('#events_calendar').datepicker('refresh');
			},'json');
		},100);
	}
	var $cal=$('#events_calendar')
		.datepicker({
			changeMonth: true,
			changeYear: true,
			onChangeMonthYear: onchange,
			beforeShowDay: function(adate) {
				var year=+adate.getFullYear(), month=+adate.getMonth()+1, day=+adate.getDate();
				if (!highlights['d'+year+'|'+month] || !highlights['d'+year+'|'+month][day]) {
					return [true, '', ''];
				}
				return [true, 'highlighted', highlights['d'+year+'|'+month][day].join(', ')];
			}
		});
	$cal.datepicker('setDate','01/01/2001');
	$cal.datepicker('setDate'); // two lines, as we want the onChangeMonthYear to trigger
	$cal.closest('tr').css('width:30%');

});
