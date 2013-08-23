$(function(){
	$('#events_wrapper').css('width:100%');
	var timer, timer2;
	var highlights=[];
	function onchange(year, month, inst) {
		clearTimeout(timer);
		if (inst!==false) {
			$('#events_list').empty();
		}
		timer=setTimeout(function(){
			$.post('/a/p=news/f=getHeadlinesMonth',{
				y:year,
				m:month,
				pid:pagedata.id
			},function(ret){
				highlights['d'+year+'|'+month]=[];
				var html='';
				for (var i=0;i<ret.length;++i) {
					var h=ret[i];
					var day=+h.adate.replace(/.*-([0-9]*) .*/, '$1');
					if (!highlights['d'+year+'|'+month][day]) {
						highlights['d'+year+'|'+month][day]=[];
					}
					highlights['d'+year+'|'+month][day].push(h.headline);
					html+='<strong>'+Core_dateM2H(h.adate, 'datetime')+'</strong>'
						+'<p><a href="'+h.url+'">'+h.headline+'</a></p>';
				}
				if (inst!==false) {
					$list=$('#events_list').html(html);
				}
				$('#events_calendar').datepicker('refresh');
			},'json');
		},100);
	}
	function onchange2(year, month, day) {
		clearTimeout(timer2);
		timer2=setTimeout(function(){
			$.post('/a/p=news/f=getHeadlinesDay',{
				y:year,
				m:month,
				d:day,
				pid:pagedata.id
			},function(ret){
				var $list=$('#events_list').empty();
				var html='';
				for (var i=0;i<ret.length;++i) {
					var h=ret[i];
					var day=+h.adate.replace(/.*-([0-9]*) .*/, '$1');
					html+='<strong>'+Core_dateM2H(h.adate, 'datetime')+'</strong>'
						+'<p><a href="'+h.url+'">'+h.headline+'</a></p>';
				}
				$list.html(html);
				onchange(year, month, false);
			},'json');
		},100);
	}
	var $cal=$('#events_calendar')
		.datepicker({
			changeMonth: true,
			changeYear: true,
			onChangeMonthYear: onchange,
			onSelect: function(dateText) {
				var year=dateText.replace(/.*\//, '');
				var month=dateText.replace(/\/.*/, '');
				var day=dateText.replace(/.*\/(.*)\/.*/, '$1');
				onchange2(year, month, day);
			},
			beforeShowDay: function(adate) {
				var year=+adate.getFullYear(), month=+adate.getMonth()+1, day=+adate.getDate();
				if (!highlights['d'+year+'|'+month] || !highlights['d'+year+'|'+month][day]) {
					return [true, '', ''];
				}
				return [true, 'ui-state-active highlighted date'+year+'-'+month+'-'+day, highlights['d'+year+'|'+month][day].join(', ')];
			}
		});
	$cal.datepicker('setDate','01/01/2001');
	$cal.datepicker('setDate'); // two lines, as we want the onChangeMonthYear to trigger
	$cal.closest('tr').css('width:30%');

});
