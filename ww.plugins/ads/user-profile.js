$(function() {
	function showMyAds() {
		$tabs.find('>div').empty();
		var $wrapper=$('#ads-main');
		$.post('/a/p=ads/f=adsGetMy', function(ret) {
			var html=[];
			for (var i=0;i<ret.length;++i) {
				var ad=ret[i];
				var link=ad.target_url
					?'<a href="'+ad.target_url+'" target="blank">link</a>'
					:'';
				ad.image_url=ad.image_url.replace(/^\/f/, '');
				var imgHtml=/swf$/.test(ad.image_url)
					?'<object type="application/x-shockwave-flash" style="max-width:400px; max-height:300px;" data="/f/'+ad.image_url+'"><param name="movie" value="/f/'+ad.image_url+'" /></object>'
					:'<img src="/a/f=getImg/w=400/h=300/'+ad.image_url+'"/>';
				html.push(
					'<table>'
					+'<tr><td rowspan="5">'+imgHtml+'</td><td>Link</td><td>'+link+'</td></tr>'
					+'<tr><td>Clicks</td><td>'+ad.clicks+'</td></tr>'
					+'<tr><td>Impressions</td><td>'+ad.views+'</td></tr>'
					+'<tr><td>Creation</td><td>'+ad.cdate+'</td></tr>'
					+'<tr><td>Expiry</td><td>'+ad.date_expire+'</td></tr>'
					+'</table>'
				);
			}
			$wrapper.html(html.join(''));
		});
	}
	function showCharts() {
		$tabs.find('>div').empty();
		var $wrapper=$('#ads-charts');
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
	}
	function showPaymentDetails() {
		$.post('/a/p=ads/f=paymentDetailsGet', function(ret) {
			$tabs.find('>div').empty();
			$('#ads-payments').html(ret);
		});
	}
	var $tabs=$('<div><ul><li><a href="#ads-main">My Ads</a></li><li><a href="#ads-charts">Charts</a></li>'
		+'<li><a href="#ads-payments">Payments</a></li></ul><div id="ads-main"/><div id="ads-charts"/>'
		+'<div id="ads-payments"/></div>')
		.appendTo('#ad-stats')
		.tabs({
			'activate':function(ev, ui) {
				switch (ui.newPanel[0].id) {
					case 'ads-main':
						showMyAds();
					break;
					case 'ads-charts':
						showCharts();
					break;
					case 'ads-payments':
						showPaymentDetails();
					break;
				}
			}
		});
	showMyAds();
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
					},
					'yaxis': {
						'min':0
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
