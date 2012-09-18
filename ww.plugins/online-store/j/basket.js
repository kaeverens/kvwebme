function os_add_one(ev){
	var tr=$(ev.target).closest('tr');
	var md5=tr.attr('product');
	var amt=tr.data('amt');
	os_set_amt(md5,amt+1,tr);
}
function os_subtract_one(ev){
	var tr=$(ev.target).closest('tr');
	var md5=tr.attr('product');
	var amt=tr.data('amt');
	os_set_amt(md5,amt-1,tr);
}
function os_remove_all(ev){
	var tr=$(ev.target).closest('tr');
	var md5=tr.attr('product');
	os_set_amt(md5,0,tr);
}
function os_set_amt(md5,amt,tr){
	$.getJSON(
		'/ww.plugins/online-store/j/set_amt.php?md5='+md5+'&amt='+amt,
		function(ret){
			if ($('#onlinestore-checkout').length) {
				document.location=document.location.toString();
			}
			amt=ret.amt;
			tr.data('amt',amt);
			$('.'+ret.md5+'-amt').text(amt);
			$('.'+ret.md5+'-item-total').text(pagedata.currency+ret.item_total);
			$('.total').text(pagedata.currency+ret.total);
			if(amt<1){
				$('tr[product="'+ret.md5+'"]').fadeOut
					(
						"normal",
						function(){
							$(this).remove();
						}
					);
			}
		}
	);
}
function os_wheres_the_basket(from){
	var f_off=from.offset();
	var f_size=[from.width(),from.height()];
	var to=$('.online-store-basket-widget');
	var t_off=to.offset();
	var t_size=[to.width(),to.height()];
	var slider=$('<div style="position:absolute;border:1px solid white;background:#ff0;opacity:.2;left:'+f_off.left+'px;top:'+f_off.top+'px;width:'+f_size[0]+'px;height:'+f_size[1]+'px"></div>').appendTo(document.body);
	slider.animate({
		left:t_off.left+'px',
		top:t_off.top+'px',
		width:t_size[0]+'px',
		height:t_size[1]+'px',
		opacity:.8
	},1000,'linear',function(){
		$(this).fadeOut('normal',function(){
			$(this).remove();
		});
	});
}
function os_reset_basket(res){
	var html='<table><tr><th>&nbsp;</th><th>'+__('Price')+'</th><th>'+__('Amount')+'</th>'
		+'<th>'+__('Total')+'</th></tr>';
	for(var md5 in res.items){
		var item=res.items[md5];
		if(md5.length!='32' || !item.amt){
			continue;
		}
		html+='<tr class="os_item_name" product="'+md5+'">'
			+'<td colspan="4"><a href="'
			+item.url+'">'+item.short_desc
			+'</a></td></tr><tr class="os_item_numbers" product="'+item.md5+'">' 
			+'<td>&nbsp;</td><td>'+pagedata.currency+item.cost
			+'</td><td class="amt">'+item.amt+'</td><td class="item-total">'
			+(item.cost*item.amt)+'</td></tr>';
	}
	html+='<tr class="os_total"><th colspan="3">'+__('Total')+'</th><td class="total">'
		+pagedata.currency+res.total
		+'</td></tr></table>'
		+'<a href="/_r?type=online-store">'+__('Proceed to Checkout')+'</a>';
	$('.online-store-basket-widget').html(html);
	os_setup_basket_events();
	__langInit();
}
function OnlineStore_saveList(){
	// TODO: translation needed
	var name=prompt('What name do you want to give to this list?', 'default');
	if (!name){
		return;
	}
	$.post('/a/p=online-store/f=saveSavedList',{
		'name': name
	}, function(ret){
		if (ret.error) {
			return alert(ret.error);
		}
		document.location=document.location.toString();
	});
}
function OnlineStore_loadList(){
	$.post('/a/p=online-store/f=listSavedLists', function(ret){
		if (ret.error) {
			return alert(ret.error);
		}
		if (!ret.names.length) {
			// TODO: translation needed
			return alert('you have no shopping lists saved');
		}
		var html='<ul>';
		for (var i=0;i<ret.names.length;++i) {
			html+='<li><a href="javascript:;">'+htmlspecialchars(ret.names[i])
				+'</a></li>';
		}
		html+='</ul>';
		$('<div id="onlinestore-load-lists"><p>'+__('Choose one of your saved lists.')+'</p>'
			+html+'</div>')
			.dialog({
				"modal":true
			});
		__langInit();
		$('#onlinestore-load-lists a').click(function(){
			$.post('/a/p=online-store/f=loadSavedList',{
				"name":$(this).text()
			}, function(ret){
				if (ret.error) {
					return alert(ret.error);
				}
				document.location=document.location.toString();
			});
		});
	});
}
function os_setup_basket_events(){
	$('tr.os_item_numbers .amt').each(function(){
		var $this=$(this);
		var contents=($this.html());
		var amt = $this.text();
		$this.html(contents
			+'<span class="amt-links">'
			+'<a href="javascript:;" class="amt-plus">+</a>'
			+'<a href="javascript:;" class="amt-minus">-</a>'
			+'<a href="javascript:;" class="amt-del">x</a>'
			+'</span>'
		);
		var tr=$this.closest('tr');
		tr.data('amt',parseInt(amt));
	});
	$('.amt .amt-plus').click(os_add_one);
	$('.amt .amt-minus').click(os_subtract_one);
	$('.amt .amt-del').click(os_remove_all);
	$('.onlinestore-save-list').click(OnlineStore_saveList);
	$('.onlinestore-load-list').click(OnlineStore_loadList);
	$('.online-store-basket-widget.slidedown .slidedown-header')
		.click(function(e, opts) {
			var $this=$(this);
			var $wrapper=$this.closest('.slidedown').find('.slidedown-wrapper');
			if ($wrapper.is(':visible')) {
				$wrapper.hide($wrapper.attr('slidedown')||'blind');
				return;
			}
			$wrapper
				.css({
					'width':$this.outerWidth(),
					'top':$this.position().top+$this.height(),
					'left':$this.position().left
				})
				.show($wrapper.attr('slidedown')||'blind');
			if (opts && opts.slideup) {
				var timeout=+$wrapper.attr('slideup');
				if (!timeout) {
					return;
				}
				setTimeout(function() {
					$this.click();
				}, timeout*1000);
			}
		});
	if (/\/showcart/.test(document.location.toString())) {
		$('.online-store-basket-widget.slidedown .slidedown-header')
			.trigger('click', {
				'slideup':true
			});
	}
}
$(os_setup_basket_events);
