$(function() {
	var $menu=$($('.menuBarTop,.menuBarLeft')[0]);
	var $menuopener=$('<div style="text-align:center;background:#000;color:#fff;position:fixed;left:0;top:0;right:0;line-height:40px;">Menu</div>');
	$menuopener.appendTo('body')
		.click(function(){
			$menu.css($menu.attr('is-open')!='true'
				?{
					'visibility':'visible',
					'height':'inherit'
				}
				:{
					'visibility':'hidden',
					'height':0
				});
			$menu.attr('is-open', $menu.attr('is-open')=='false');
		});
	$('h3').each(function() {
		$wrapper=$('<div class="hwrapper"/>')
			.append($(this).nextUntil('h1,h2,h3'))
			.insertAfter(this)
			.hide();
		$(this).click(function() {
			$(this).next().toggle(200);
		});
	});
	$('h2').each(function() {
		$wrapper=$('<div class="hwrapper"/>')
			.append($(this).nextUntil('h1,h2'))
			.insertAfter(this)
			.hide();
		$(this).click(function() {
			$(this).next().toggle(200);
		});
	});
});
