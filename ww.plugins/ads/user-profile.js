$(function() {
	var $wrapper=$('#ad-stats');
	var html='<table style="width:100%"><tr>'
		+'<th>From:</th><td><input id="ad-stats-from" class="date"/></td>'
		+'<th>To:</th><td><input id="ad-stats-to" class="date"/></td>'
		+'<th>Type:</th><td><select id="ad-stats-type"><option value=""> -- all -- </option><option selected="selected">click</option><option>view</option></select></td>'
		+'<th>Ad:</th><td><select id="ad-stats-ad"></select></td>'
		+'</tr>'
		+'<tr><td colspan="8"><div id="ad-stats-chart">'
		+'</div></td></tr>'
		+'</table>';
	$wrapper.append(html);
	var toDate=new Date();
	var fromDate=new Date(toDate.getTime()-3600*24*31*6*1000);
	$('#ad-stats-to').val(toDate.getFullYear()+'-'+(toDate.getMonth()+1)+'-'+toDate.getDate());
	$('#ad-stats-from').val(fromDate.getFullYear()+'-'+(fromDate.getMonth()+1)+'-'+fromDate.getDate());
	$('#ad-stats-type').change(showStats);
	$('#ad-stats-to, #ad-stats-from').change(showStats).datepicker({
		'dateFormat': 'yy-mm-dd'
	});
	$.post('/a/p=ads/f=adsGetMy', function(ret) {
		var opts=['<option value="0"> -- all -- </option>'];
		$.each(ret, function(k, v) {
			opts.push('<option value="'+v.id+'">'+v.name+' ('+v.clicks+')</option>');
		});
		$('#ad-stats-ad').html(opts.join('')).change(showStats).change();
	});
	function showStats() {
		if (!$.jqplot) {
			$.cachedScript(
				'/j/jquery.jqplot/jquery.jqplot.min.js',
				function() {
					$.cachedScript(
						'/j/jquery.jqplot/jqplot.dateAxisRenderer.min.js',
						showStats
					);
				}
			);
			return;
		}
		var fromDate=$('#ad-stats-from').val(), toDate=$('#ad-stats-to').val();
		$('#ad-stats-chart').empty();
		$.get('/a/p=ads/f=statsGet', {
			'from':fromDate,
			'to':toDate,
			'ad':$('#ad-stats-ad').val()
		}, function(ret) {
			var from=new Date(
				fromDate.replace(/\-.*/, ''), fromDate.replace(/.*\-(.*)\-.*/, '$1')-1,
				fromDate.replace(/.*\-.*\-(.*)/, '$1')
			);
			var to=new Date(
				toDate.replace(/\-.*/, ''), toDate.replace(/.*\-(.*)\-.*/, '$1')-1,
				toDate.replace(/.*\-.*\-(.*)/, '$1')
			);
			if (from>=to) {
				return $('#ad-stats-chart').empty().html('invalid dates');
			}
			var data={};
			to.setHours(6);
			do {
				var y=from.getFullYear(), m=from.getMonth()+1, d=from.getDate();
				if (m<10) {
					m='0'+m;
				}
				if (d<10) {
					d='0'+d;
				}
				var d=y+'-'+m+'-'+d;
				from.setDate(from.getDate()+1);
				data[d]=0;
			} while(from<to);
			var agg={};
			var type=$('#ad-stats-type').val();
			var selectedId=+$('#ad-stats-ad').val();
			for (var i=0;i<ret.length;++i) {
				var s=ret[i];
				var ad_id=+s.ad_id;
				if (selectedId && selectedId!=ad_id) {
					continue;
				}
				var d=s.cdate.replace(/ .*/, '');
				if (!agg[d]) {
					agg[d]=[];
				}
				if (!agg[d][ad_id]) {
					agg[d][ad_id]={
						'view':0,
						'click':0
					};
				}
				if (+s.click) {
					agg[d][ad_id].click++;
					if (type=='' || type=='click') {
						data[d]+= +s.click;
					}
				}
				if (+s.view) {
					agg[d][ad_id].view++;
					if (type=='' || type=='view') {
						data[d]+= +s.view;
					}
				}
			}
			var points=[];
			$.each(data, function(k, v) {
				points.push([k, v]);
			});
			var plot1=$.jqplot('ad-stats-chart', [points], {
				'axes':{
					'xaxis': {
						'renderer':$.jqplot.DateAxisRenderer
					}
				},
				'series':[
					{
						'lineWidth':1,
						'color':'#f00',
						'markerOptions': {
							show:false
						}
					}
				]
			});
		});
	}
});
