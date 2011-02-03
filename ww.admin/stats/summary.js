function show_data(d_name){
	window.plot_data=window[d_name];
	window.plot_options = {
		xaxis: { mode: "time" },
		selection: { mode: "x" },
		grid: { markings: weekendAreas },
		legend: { position: 'nw' }
	};
	window.plot = $.plot($("#placeholder"), [
		{data:window.plot_data, label:d_name},
	], window.plot_options);
	window.plot_overview = $.plot($("#overview"), [window.plot_data], {
		lines: { show: true, lineWidth: 1 },
		shadowSize: 0,
		xaxis: { ticks: [], mode: "time" },
		yaxis: { ticks: [], min: 0, max: window['max_'+d_name]},
		selection: { mode: "x" }
	});
}
	function weekendAreas(axes) {
		var markings = [];
		var d = new Date(axes.xaxis.min);
		d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
		d.setUTCSeconds(0);
		d.setUTCMinutes(0);
		d.setUTCHours(0);
		var i = d.getTime();
		do {
			// when we don't set yaxis the rectangle automatically
			// extends to infinity upwards and downwards
			markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
			i += 7 * 24 * 60 * 60 * 1000;
		} while (i < axes.xaxis.max);

		return markings;
	}
	$("#placeholder").bind("plotselected", function (event, ranges) {
		window.plot = $.plot($("#placeholder"), [window.plot_data],
			$.extend(true, {}, window.plot_options, {
				xaxis: { min: ranges.xaxis.from, max: ranges.xaxis.to }
			}));
		window.plot_overview.setSelection(ranges, true);
	});
	$("#overview").bind("plotselected", function (event, ranges) {
		window.plot.setSelection(ranges);
	});
show_data('all_visitors');
