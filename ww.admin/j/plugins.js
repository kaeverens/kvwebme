function show_help(p, t){
	var h=parseInt($(window).height() * .7), w=parseInt(document.body.offsetWidth * .9);
	$('<div><iframe style="width:'+w+'px;height:'+h+'px" src="/ww.docs/plugin.php?t='+t+'&p='+p+'"></iframe></div>')
		.dialog({
			width:w+40,
			height:h+60,
			modal:true
		});
}
