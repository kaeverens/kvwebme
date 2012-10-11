$(function(){
	function updatePrice() {
		var $wrapper=$(this).closest('.products-product');
		var $price=$wrapper.find('strong.os_price');
		var $submit=$wrapper.find('.submit-button');
		var vat=+$submit.attr('vat');
		var price=+$submit.attr('price').replace(/[^0-9\.]/g, '');
		var currency=$submit.attr('price').replace(/[0-9\.]/g, '');
		$wrapper.find('select').each(function() {
			var val=$(this).val();
			if (!/\|/.test(val)) {
				return;
			}
			price+= +(val.split('|')[1]);
		});
		var amt=+$wrapper.find('[name=products-howmany]').val();
		if (!amt) {
			amt=1;
		}
		price*=amt;
		if ($price.hasClass('vat')) {
			price*=1+(vat/100);
		}
		var n=Math.round(price*100)/100;
		if (!/\./.test(n)) {
			n+='.';
		}
		n+='00';
		n=n.replace(/\.(..).*/, '.$1');
		$price.text(currency+n);
	}
	var cache={}, lastXhr;
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
			var $this=$(this), $form=$this.closest('form');
			if(!$form.length){
				$form=$this.wrap('<form style="display:inline" action="'+
					(document.location.toString())+'" />');
			}
			setTimeout(function(){
				$this.closest('form').submit();
			}, 500);
		});
	// { on rollover of submit button
	$('div.products-product form input[type=submit], div.products-product form button.submit-button').live('mouseover', function(){
		var inps=[];
		var $form=$(this).closest('form');
		$form.find('input').each(function(){
			if (/products_values_/.test(this.name)) {
				$(this).remove();
			}
		});
		$(this).closest('div.products-product').find('select, input, textarea').each(function(){
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
	// }
	var paddtocart=0;
	$('form.products-addtocart, form.products-addmanytocart').submit(function(){
		var $this=$(this);
		var $amt=$this.find('[name=products-howmany]');
		if (!$amt.length) {
			var $wrapper=$(this).closest('.products-product');
			var amt=+$wrapper.find('[name=products-howmany]').val();
			if (!amt) {
				amt=1;
			}
			$this.append(
				'<input type="hidden" name="products-howmany" value="'+amt+'"/>'
			);
		}
		var found=0;
		var redirect=$this.find('input[name=products_redirect]').val();
		$this.find('input.required').each(function(){
			if (!$(this).val()) {
				found=1;
			}
		});
		if (found) { // blank required fields found
			alert('please enter all required fields');
			return false;
		}
		$.post('/a/p=online-store/f=addProductToCart',
			$this.serializeArray(),
			function(ret){
				if (ret && ret.ok) {
					document.location=redirect=='checkout'
						?'/_r?type=online-store'
						:document.location.toString().replace('/showcart', '')+'/showcart';
					return;
				}
				if (ret.error=='expired') {
					$.post(
						'/a/p=online-store/f=getExpiryNotification',
						{'id':$this.find('input[name=product_id]').val()},
						function(ret) {
							$('<div>'+ret+'</div>').dialog({
								'modal':true
							});
						}
					);
				}
			}
		);
		return false;
	});
	$('.products-product select').live('change', updatePrice);
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
						setTimeout(rotate, 5000);
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
			var years=parseInt(diff/1000/3600/24/365);
			diff-=years*1000*3600*24/365;
			var days=parseInt(diff/1000/3600/24);
			diff-=days*1000*3600*24;
			var hours=parseInt(diff/1000/3600);
			diff-=hours*1000*3600;
			var minutes=parseInt(diff/1000/60);
			diff-=minutes*1000*60;
			var seconds=parseInt(diff/1000);
			var show=years?years+'y ':'';
			show+=days?days+'d ':'';
			show+=hours<10?'0'+hours:hours;
			show+=':';
			show+=minutes<10?'0'+minutes:minutes;
			show+=':';
			show+=seconds<10?'0'+seconds:seconds;
			$this.html(show);
			setTimeout(update, 1000);
		}
		update();
	});
	$('ul.carousel').each(function() {
		var $this=$(this);
		$this.jcarousel();
		var sequence=[];
		var $wrap=$this.closest('.products-product').find('.products-image');
		var bigw=$wrap.width(), bigh=$wrap.height();
		$this.find('img').each(function() {
			var src=$(this).css('background-image')
				.replace(/^url\("?|w=[0-9]*\/h=[0-9]*\/|"?\)$/g, '');
			$(this).click(function() {
				$wrap.find('img').attr('src', src+'/w='+bigw+'/h='+bigh);
				$wrap.find('a').attr('href', src);
			});
			sequence.push(src);
		});
		$wrap.find('a').data('sequence', sequence);
	});
	Products_showMap();
	$('#products-carousel-slider').each(function() {
		var $this=$(this);
		$this.jcarousel({
			itemFallbackDimension: 180,
			wrap: 'circular',
			animation:1000
		});
	});
	$('.products-product.stock-control').each(function() {
		// { get details
		var $this=$(this), $stockcontrol=$this.find('input.stock-control-total'),
			$qty=$this.find('.add_multiple_widget_amount'),
			details=$stockcontrol.attr('details');
		if (!details) {
			details='[]';
		}
		details=eval(details);
		var total=+$stockcontrol.val();
		if (!total) {
			$this.find('select, input, button').attr('disabled', true);
			return;
		}
		// }
		// { change selectboxes if applicable
		var options=[];
		function recheck() {
			var $this=$(this),
				name=$this.attr('name').replace('products_values_', '');
			var selected=[];
			for (var i=0;i<options.length;++i) {
				selected.push(
					$('select[name=products_values_'+options[i]+']')
						.val().replace(/\|.*/, '')
				);
				if (name==options[i]) {
					i++;
					break;
				}
			}
			if (i>=options.length) {
				return;
			}
			name=options[i];
			var $select=$('select[name=products_values_'+options[i]+']');
			var $options=$select.find('option');
			$options.attr('disabled', true);
			var selectedOpt=false;
			for (var i=0;i<details.length;++i) {
				if (+details[i]._amt<1) {
					continue;
				}
				var mismatch=0;
				for (j=0;j<selected.length;++j) {
					if (details[i][options[j]]!=selected[j]) {
						mismatch=1;
					}
				}
				if (mismatch) {
					continue;
				}
				for (var j=0;j<$options.length;++j) {
					if ($options[j].value.replace(/\|.*/, '')==details[i][name]) {
						$($options[j]).attr('disabled', false);
					}
				}
			}
			for (j=0;j<$options.length;++j) {
				if (!$($options[j]).attr('disabled')) {
					$select.val($options[j].value);
					break;
				}
			}
			$qty.val(1);
		}
		if (details.length) {
			$.each(details[0], function(k, v) {
				if (k=='_amt') {
					return;
				}
				$this.find('select[name=products_values_'+k+']')
					.change(recheck)
					.find('option').attr('disabled', true);
				options.push(k);
			});
			var found;
			var $options=$this
				.find('select[name=products_values_'+options[0]+'] option');
			var selectedOpt=false;
			for (var i=0;i<details.length;++i) {
				if (+details[i]._amt) {
					for (var j=0;j<$options.length;++j) {
						if ($options[j].value.replace(/\|.*/, '')==details[i][options[0]]) {
							if (!selectedOpt) {
								selectedOpt=$options[j].value;
							}
							$($options[j]).attr('disabled', false);
						}
					}
				}
			}
			$this.find('select[name=products_values_'+options[0]+']').val(selectedOpt);
			$('select[name=products_values_'+options[0]+']').change();
		}
		// }
		// { make sure no more than is available can be added to cart
		$qty.change(function() {
			var qty=+$qty.val();
			var match=null;
			for (var i=0;i<details.length;++i) {
				var match=details[i];
				$.each(details[i], function(k, v) {
					if (k=='_amt') {
						return;
					}
					var val=$this.find('select[name=products_values_'+k+']').val();
					val=val.replace(/\|.*/, '');
					if (val!=v) {
						match=null;
					}
				});
				if (match) {
					break;
				}
			}
			if (+match._amt<qty) {
				return $('<p>only '+match._amt+' in stock</p>').dialog({
					'modal':true
				});
			}
		});
		// }
	});
});
function Products_showMap() {
	var $mapview=$('#products-mapview');
	if (!$mapview.length) {
		return;
	}
	if (!window.google || !google.maps) {
		$('<script src="http://maps.googleapis.com/maps/api/js?sensor=true&c'
			+'allback=Products_showMap"></script>')
			.appendTo(document.body);
		return;
	}
	if ($mapview.length) {
		var width=$mapview.width(), height=$mapview.height();
		if (height<100) {
			$mapview.css('min-height', 100);
		}
	}
	var latlng=window.userdata&&window.userdata.lat&&window.userdata.lng
		?[window.userdata.lat, window.userdata.lng]
		:[54.78310263573059, -6.278343984374946];
	var myOptions={
		zoom:10,
		center:new google.maps.LatLng(latlng[0], latlng[1]),
		mapTypeId:google.maps.MapTypeId.ROADMAP
	};
	var map=new google.maps.Map($mapview[0], myOptions);
	var markers=[];
	google.maps.event.addListener(map, 'bounds_changed', function(){
		clearTimeout(window.products_boundsChanged);
		window.products_boundsChanged=setTimeout(function(){
			var bounds=map.getBounds();
			var coords=bounds.toString().replace(/[^0-9\.\-,]/g, '').split(',');
			$.post('/a/p=products/f=getProductOwnersByCoords', {
				'coords':coords
			}, function(ret) {
				for (var i=0;i<ret.length;++i) {
					var user_id=+ret[i].id;
					if (markers[user_id]) {
						continue;
					}
					var marker=new google.maps.Marker({
						position:new google.maps.LatLng(ret[i].location_lat, ret[i].location_lng), 
						map:map,
						user_id:user_id
					});
					google.maps.event.addListener(marker, 'click', function() {
						var user_id=+this.user_id;
						var marker=this;
						$.post('/a/p=products/f=getProductsByUser/user_id='+user_id, function(ret) {
							var content='';
							for (var i=0;i<ret.length;++i) {
								content+='<li><a href="'+ret[i].url+'">'
									+'<img src="/a/p=products/f=showDefaultImg/id='+ret[i].id+'/w=32/h=32"/>'
									+ret[i].name+'</a></li>';
							}
							var infoWindow=new google.maps.InfoWindow({
								content: '<ul style="margin:0;padding:0;list-style:none;">'+content+'</ul>'
							});
							infoWindow.open(map, marker);
						});
					});
					markers[user_id]=marker;
				}
			});
		}, 500);
	});
	if (navigator.geolocation) {
		browserSupportFlag = true;
		navigator.geolocation.getCurrentPosition(function(position) {
			map.setCenter(
				new google.maps.LatLng(
					position.coords.latitude,
					position.coords.longitude
				)
			);
		}, function() {
		});
	}
}
stub('editWatchlists', 'Products');
