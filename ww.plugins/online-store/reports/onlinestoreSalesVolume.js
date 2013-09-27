function Reports_onlinestoreSalesVolume($el) {
	var $content=$el;
	function update() {
		var from=$('#onlinestore-numberofsales-from').val(),
			to=$('#onlinestore-numberofsales-to').val();
		if (!from) {
			var d=new Date();
			$('<table class="wide smalltext"><tr>'+
				'<th>'+__('From')+'</th>'+
				'<td><input class="date" id="onlinestore-numberofsales-from"/></td>'+
				'<th>'+__('To')+'</th>'+
				'<td><input class="date" id="onlinestore-numberofsales-to"/></td>'+
				'</tr></table>'+
				'<div id="onlinestore-numberofsales-chart" style="position:absolute;left:0;'+
				'bottom:0;right:0;top:25px"/>'
			).appendTo($content);
			to=$('#onlinestore-numberofsales-to')
				.val(d.toYMD())
				.datepicker({
					'dateFormat':'yy-mm-dd',
					'onSelect':function() {
						Reports_onlinestoreSalesVolume($content);
					}
				})
				.val();
			d.setDate(d.getDate()-31);
			from=$('#onlinestore-numberofsales-from')
				.val(d.toYMD())
				.datepicker({
					'dateFormat':'yy-mm-dd',
					'onSelect':function() {
						Reports_onlinestoreSalesVolume($content);
					}
				})
				.val();
		}
		$.post('/a/p=online-store/f=adminReportNumberOfSales', {
			'from':from,
			'to':to
		}, function(ret) {
			var line1=[];
			var max=0;
			$.each(ret, function(key, val) {
				val=+val;
				line1.push([key, val]);
				if (val>max) {
					max=val;
				}
			});
			$('#onlinestore-numberofsales-chart').empty();
			if (line1.length<2) {
				return $('#onlinestore-numberofsales-chart')
					// TODO: translation needed
					.html('Not enough data to create a chart');
			}
			$.jqplot.config.enablePlugins = true;
			$.jqplot('onlinestore-numberofsales-chart', [line1], {
				'axes':{
					'xaxis': {
						'renderer':$.jqplot.DateAxisRenderer
					},
					'yaxis': {
						'min':0,
						'max':max
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
	if ($.jqplot) {
		update();
	}
	else {
		$.cachedScript(
			'/j/jquery.jqplot/jquery.jqplot.min.js',
			function() {
				$.cachedScript(
					'/j/jquery.jqplot/jqplot.dateAxisRenderer.min.js',
					function() {
						$.cachedScript(
							'/j/jquery.jqplot/jqplot.trendline.min.js',
							function() {
								Reports_onlinestoreSalesVolume($content);
							}
						);
					}
				);
			}
		);
	}
}
