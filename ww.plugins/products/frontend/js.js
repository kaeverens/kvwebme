$(function(){
	function updatePrice() {
		var $wrapper=$(this).closest('.products-product');
		var $price=$wrapper.find('strong.os_price');
		var $submit=$wrapper.find('.submit-button');
		var price=+$submit.attr('price').replace(/[^0-9\.]/g, '');
		var currency=$submit.attr('price').replace(/[0-9\.]/g, '');
		$wrapper.find('select').each(function() {
			var val=$(this).val();
			if (!/\|/.test(val)) {
				return;
			}
			price+= +(val.split('|')[1]);
		});
		$price.text(currency+price);
	}
	$('a.products-lightbox').lightBox();
	$('div.product-images img').click(function(){
		var src=$('a.products-lightbox img').attr('src'),
			id=this.src.replace(/.*kfmget\/([0-9]*)[^0-9].*/,'$1'),
			$wrapper=$(this).closest('.products-product')
			caption=this.title;
		$wrapper
			.find('a.products-lightbox').attr('href','/kfmget/'+id)
			.find('img').attr('src',src.replace(/kfmget\/([0-9]*)/,'kfmget/'+id));
		$wrapper
			.find('span.caption').html(caption);
	});
	var cache={},lastXhr;
	$('input[name=products-search]')
		.autocomplete({
			source: function(request, response){
				var term = request.term;
				if ( term in cache ) {
					response( cache[ term ] );
					return;
				}
				lastXhr = $.getJSON( 
					"/ww.plugins/products/frontend/search.php", 
					request, 
					function( data, status, xhr ) {
						cache[ term ] = data;
						if ( xhr === lastXhr ) {
							response( data );
						}
					}
				);
			}
		})
		.focus(function(){
			this.value='';
		})
		.change(function(){
			var $this=$(this)
				,$form=$this.closest('form');
			if(!$form.length){
				$form=$this.wrap('<form style="display:inline" action="'+
					(document.location.toString())+'" />');
			}
			setTimeout(function(){
				$this.closest('form').submit();
			},500);
		});
	$('div.products-product form input[type=submit],div.products-product form button').live('mouseover',function(){
		var inps=[];
		var $form=$(this).closest('form');
		$form.find('input').each(function(){
			if (/products_values_/.test(this.name)) {
				$(this).remove();
			}
		});
		$(this).closest('div.products-product').find('select,input,textarea').each(function(){
			if (!/products_values_/.test(this.name)) {
				return;
			}
			inps.push([this.name, $(this).val(), this.className]);
		});
		for (var i=0;i<inps.length;++i) {
			$('<input type="hidden" name="'+inps[i][0]+'" />')
				.val(inps[i][1])
				.addClass(inps[i][2])
				.appendTo($form);
		}
	});
	var paddtocart=0;
	$('form.products-addtocart,form.products-addmanytocart').submit(function(){
		var $this=$(this);
		var found=0;
		$this.find('input.required').each(function(){
			if (!$(this).val()) {
				found=1;
			}
		});
		if (found) { // blank required fields found
			alert('please enter all required fields');
			return false;
		}
		$.post('/a/f=nothing',
			$this.serializeArray(),
			function(){
				document.location=document.location.toString()
					.replace('/showcart', '')+'/showcart';
			}
		);
		return false;
	});
	$('.products-product select').change(updatePrice);
	$('.products-product').each(function() {
		$($(this).find('select')[0]).change();
	});
	$('.products-image-slider').each(function() {
		var $this=$(this);
		$.post('/a/p=products/f=getImgs/id='+$this.closest('.products-product').attr('id').replace(/products-/, ''), function(ret) {
			if (ret.length<1) {
				return;
			}
			var $imgs=[];
			var imgat=0;
			var height=$this.height(), width=$this.width();
			for (var i=0;i<ret.length;++i) {
				$imgs.push($('<img src="'+ret[i]+'" style="position:absolute;left:0;top:0;width:'+width+'px;height:'+height+'px;opacity:0"/>').appendTo($this));
			}
			$this.css({
				'position':'relative',
				'overflow':'hidden'
			});
			function rotate() {
				$imgs[imgat].animate({
					'left':-width+'px'
				}, 200);
				imgat=(imgat+1)%$imgs.length;
				$imgs[imgat]
					.css({
						'left':width+'px'
					})
					.animate({
						'opacity':1,
						'left':0
					}, 200, function() {
						if ($imgs.length<2) {
							return;
						}
						setTimeout(rotate, 2000);
					});
			}
			rotate();
		});
	});
	$('.products-expiry-clock').each(function() {
		var $this=$(this);
		var text=$this.text();
		if (text=='0000-00-00 00:00:00' || text=='') {
			$this.html($this.attr('unlimited'));
			return;
		}
		var bits=text.split(/[:\- ]/);
		var d=new Date(bits[0], bits[1]-1, bits[2], bits[3], bits[4]);
		function update() {
			var now=new Date();
			var diff=d-now;
			var days=parseInt(diff/1000/3600/24);
			diff-=days*1000*3600*24;
			var hours=parseInt(diff/1000/3600);
			diff-=hours*1000*3600;
			var minutes=parseInt(diff/1000/60);
			diff-=minutes*1000*60;
			var seconds=parseInt(diff/1000);
			$this.html(days+'d, '+hours+'h, '+minutes+'m, '+seconds+'s');
			setTimeout(update, 1000);
		}
		update();
	});
	$('ul.carousel').each(function() {
		var $this=$(this);
		$this.jcarousel();
		var sequence=[];
		var $wrap=$this.closest('.products-product').find('.products-image');
		var bigw=$wrap.attr('width'), bigh=$wrap.attr('height');
		$this.find('img').each(function() {
			var src=$(this).css('background-image')
				.replace(/^url\("|w=[0-9]*\/h=[0-9]*\/|"\)$/g, '');
			$(this).click(function() {
				$wrap.find('img').attr('src', src+'/w='+bigw+'/h='+bigh);
				$wrap.find('a').attr('href', src);
			});
			sequence.push(src);
		});
		$wrap.find('a').data('sequence', sequence);
	});
	Products_showMap();
});
function Products_showMap() {
	var $mapview=$('#products-mapview');
	if ($mapview.length) {
		var width=$mapview.width(), height=$mapview.height();
		if (height<100) {
			$mapview.css('min-height', 100);
		}
	}
	// { lat/long
	if (!window.google || !google.maps) {
		$('<script src="http://maps.googleapis.com/maps/api/js?sensor=true&c'
			+'allback=Products_showMap"></script>')
			.appendTo(document.body);
		return;
	}
	var latlng=window.userdata
		?[window.userdata.lat, window.userdata.lng]
		:[0,0]
	var myOptions={
		zoom:8,
		center:new google.maps.LatLng(latlng[0], latlng[1]),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};
	var map=new google.maps.Map($mapview[0], myOptions);
	var markers=[];
	google.maps.event.addListener(map, 'bounds_changed', function(){
		var bounds=map.getBounds();
		var coords=bounds.toString().replace(/[^0-9\.\-,]/g, '').split(',');
		$.post('/a/p=products/f=getProductsByCoords', {
			'coords':coords
		}, function(ret) {
			console.log(ret);
		});
	});
	// }
}
