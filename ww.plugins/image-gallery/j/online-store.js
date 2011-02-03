function ig_setup_ad_store(){
	if(ig_prices.length<1)return; // no prices
	var html='<table class="ig-ad-add-to-cart" style="position:absolute;right:0;z-index:101">';
	if(ig_prices.length>1){
		var i;
		// { description
		html+='<tr><th>Type</th><td><select id="ig-ad-desc"><option value=""> -- please choose -- </option>';
		for(i=0;i<ig_prices.length;++i){
			var j=ig_prices[i];
			html+='<option value="'+i+'">'+pagedata.currency+j[1]+' - '+j[0]+'</option>';
		}
		html+='</select></td></tr>';
		// }
	}
	else{
		html+='<tr style="display:none"><td></td><td><input id="ig-ad-desc" value="0" /></td></tr>';
	}
	// { how many
	html+='<tr><th>How Many</th><td><input id="ig-ad-howmany" value="1" size="3" /></td></tr>';
	// }
	// { add to cart
	html+='<tr><th colspan="2"><button id="ig-ad-addtocart">Add To Cart</button></th></tr>';
	// }
	html+='</table>';
	$('.ad-gallery')
		.css('position','relative')
		.prepend($(html));
	$('#ig-ad-addtocart').click(ig_ad_add_to_cart);
}
function ig_ad_add_to_cart(){
	var t=$('#ig-ad-desc').val(),amt=$('#ig-ad-howmany').val();
	if(t=='')return alert('Please choose a type');
	t= +t;
	if(!ig_prices[t])return alert('no hacking please');
	var type=ig_prices[t];
	amt=parseInt(+amt);
	if(amt<1)return alert('how many would you like?');
	var img=$('.ad-image img');
	if(img.length!=1)return alert('please choose an item');
	img=parseInt(img.attr('src').replace(/.*\//,''));
	$.getJSON('/ww.plugins/image-gallery/j/add-to-cart.php',{
		't_cost':type[1],
		't_desc':type[0],
		'amt':amt,
		'img':img,
		'pid':pagedata.id
	},ig_reset_cart);
}
function ig_reset_cart(res){
	os_reset_basket(res);
	os_wheres_the_basket($('#ig-ad-addtocart'));
}
$(document).ready(function(){
	if($('.ad-gallery').length)ig_setup_ad_store();
});
