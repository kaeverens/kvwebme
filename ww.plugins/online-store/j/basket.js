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
	var slider=$('<div style="position:absolute;border:1px solid white;background:#ff0;opacity:.2;left:'+f_off.left+'px;top:'+f_off.top+'px;width:'+f_size[0]+'px;height:'+f_size[1]+'px">TEST</div>').appendTo(document.body);
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
	var html='<table><tr><th>&nbsp;</th><th>Price</th><th>Amount</th>'
		+'<th>Total</th></tr>';
	for(var md5 in res.items){
		var item=res.items[md5];
		if(md5.length!='32' || !item.amt){
			continue;
		}
		html+='<tr class="os_item_name" product="'+md5+'">'
			+'<td colspan="4"><a href="'
			+item.url+'">'+item.short_desc
			+'</a></td></tr><tr class="os_item_numbers" product="'+ret.md5+'">' 
			+'<td>&nbsp;</td><td>'+pagedata.currency+item.cost
			+'</td><td class="amt">'+item.amt+'</td><td class="item-total">'
			+(item.cost*item.amt)+'</td></tr>';
	}
	html+='<tr class="os_total"><th colspan="3">Total</th><td class="total">'
		+pagedata.currency+res.total
		+'</td></tr></table>'
		+'<a href="/_r?type=online-store">Proceed to Checkout</a>';
	$('.online-store-basket-widget').html(html);
	os_setup_basket_events();
}
function os_setup_basket_events(){
	$('tr.os_item_numbers .amt').each(function(){
		var $this=$(this);
		var contents=($this.html());
		var amt = $this.text();
		$this.html(contents
			+'<span class="amt-links">('
			+'<a href="javascript:;" class="amt-plus">+</a>|'
			+'<a href="javascript:;" class="amt-minus">-</a>|'
			+'<a href="javascript:;" class="amt-del">x</a>'
			+')</span>'
		);
		var tr=$this.closest('tr');
		tr.data('amt',parseInt(amt));
	});
	$('.amt .amt-plus').click(os_add_one);
	$('.amt .amt-minus').click(os_subtract_one);
	$('.amt .amt-del').click(os_remove_all);
}
$(os_setup_basket_events);
