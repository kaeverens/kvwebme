$(function() {
	var $wrapper=$('#ads-purchase-wrapper');
	var types=[];
	var chosenType=false;
	var user=userdata.id
		?''
		:'<tr><th>Email address</th><td><input type="email" id="ads-purchase-email"'
		+'/></td></tr>';
	var html='<table>'
		+user
		+'<tr><th>Ad Type</th><td><select id="ads-purchase-type_id"/></td></tr>'
		+'<tr><th>Price</th><td id="ads-purchase-price_per_day"></td></tr>'
		+'<tr><th>How many Days?</th><td><input type="number" style="width:60px"'
		+' id="ads-purchase-days-wanted" value="7"/></td></tr>'
		+'<tr><th>Target Type</th><td><select id="ads-purchase-target_type">'
		+'<option value="0">Website</option><option value="1">Poster image</option>'
		+'</select></td></tr>'
		+'<tr><th id="ads-purchase-target_url-header">Target URL</th><td>'
		+'<input id="ads-purchase-target_url" style="width:300px"'
		+' value="http://yourwebsiteaddress/" />'
		+'<div id="ads-purchase-poster-wrapper"><span id="ads-purchase-poster"/>'
		+'<span id="ads-purchase-poster-preview"/></div></tr>'
		+'<tr><th>Your Image</th><td><span id="ads-purchase-image"/>'
		+'<span id="ads-purchase-size"/></td></tr>'
		+'<tr><th>Preview</th><td id="ads-purchase-preview"></td></tr>'
		+'<tr><th>Subtotal</th><td id="ads-purchase-subtotal"></td></tr>'
		+'<tr><th>Purchase</th><td id="ads-purchase-purchase"></td></tr>'
		+'</table>';
	$(html).appendTo($wrapper);
	$.get('/a/p=ads/f=typesGet', function(ret) {
		var opts='<option></option>';
		$.each(ret, function(k, v) {
			opts+='<option value="'+v.id+'">'+v.name+'</option>';
			v.price_per_day=+v.price_per_day;
			types[+v.id]=v;
		});
		$('#ads-purchase-days-wanted').change(function() {
			if (!chosenType) {
				$('#ads-purchase-subtotal, #ads-purchase-purchase')
					.addClass('disabled')
					.html('please choose an ad type');
				updatePreview();
				return;
			}
			var days=$('#ads-purchase-days-wanted').val();
			var subtotal=days*chosenType.price_per_day;
			$('#ads-purchase-subtotal').removeClass().html('€'+subtotal);
			updatePreview();
			site_url=document.location.toString()
				.replace(/(https?:\/\/[^\/]*).*/, '$1');
			// { paypal form
			var paypal='<form method="post" action="https://www.pay'
				+'pal.com/cgi-bin/webscr"><input type="hidden" value="_xclick" name="cmd"'
				+'/><input type="hidden" value="'+ads_paypal+'" name="business"/>'
				+'<input type="hidden" value="Ads Purchase" name="item_name"/>'
				+'<input type="hidden" id="paypal-order-id" value="" name="item_number"/>'
				+'<input type="hidden" value="'+subtotal+'" name="amount"/>'
				+'<input type="hidden" value="EUR" name="currency_code"/>'
				+'<input type="hidden" value="1" name="no_shipping"/>'
				+'<input type="hidden" value="1" name="no_note"/>'
				+'<input type="hidden" name="return" value="'+site_url+'" />'
				+'<input type="hidden" value="'+site_url
				+'/ww.plugins/ads/verify/paypal.php" name="notify_url"/>'
				+'<input type="hidden" value="IC_Sample" name="bn"/><input type="image" a'
				+'lt="Make payments with payPal - it\'s fast, free and secure!" name="sub'
				+'mit" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="'
				+'width:68px;height:23px;"/><img w'
				+'idth="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" '
				+'alt=""/></form>';
			// }
			var $paypal=$(paypal).appendTo($('#ads-purchase-purchase').empty().removeClass());
			$paypal.find('input').click(function() {
				$.post('/a/p=ads/f=makePurchaseOrder', {
					'type_id':$('#ads-purchase-type_id').val(),
					'days':$('#ads-purchase-days-wanted').val(),
					'target_url':$('#ads-purchase-target_url').val(),
					'target_type':$('#ads-purchase-target_type').val(),
					'email':$('#ads-purchase-email').val()
				}, function(ret) {
					$('#paypal-order-id').val(ret.id).closest('form').submit();
				});
				return false;
			});
		});
		$('#ads-purchase-type_id').html(opts)
			.change(function() {
				var id=+$(this).val();
				if (!id) {
					$('#ads-purchase-price_per_day,#ads-purchase-size, #ads-purchase-preview')
						.addClass('disabled')
						.html('please choose an ad type');
					chosenType=false;
					$('#ads-purchase-days-wanted').change();
					updatePreview();
					return;
				}
				chosenType=types[id];
				$('#ads-purchase-price_per_day')
					.removeClass()
					.html('€'+chosenType.price_per_day+' per day');
				$('#ads-purchase-size')
					.removeClass()
					.html(chosenType.width+'px x '+chosenType.height+'px');
				$('#ads-purchase-days-wanted').change();
				updatePreview();
			})
			.change();
		$('#ads-purchase-target_type')
			.change(function() {
				var type=+$(this).val();
				switch(type) {
					case 1:
						$('#ads-purchase-target_url').css('display', 'none');
						$('#ads-purchase-poster-wrapper').css('display', 'block');
						$('#ads-purchase-target_url-header').html('Poster Image<br/>(max 800x800)');
					break;
					default:
						$('#ads-purchase-target_url').css('display', 'block');
						$('#ads-purchase-poster-wrapper').css('display', 'none');
						$('#ads-purchase-target_url-header').text('Website Address');
					break;
				}
			})
			.change();
		$('#ads-purchase-email').change(function() {
			$.post('/a/f=userGetUid', {
				'email':$(this).val()
			}, function(ret) {
				if (ret.error) {
					return alert(ret.error);
				}
				if (ret.uid && !(/tmp-/.test(ret.uid))) {
					var html='<p>This email address is already registered in the'
						+' database. Please <a href="/_r?type=loginpage">Log In</a>'
						+' before creating an ad.</p>';
					return $(html).dialog({
						'modal': true,
						'buttons':{
							'Login': function() {
								document.location='/_r?type=loginpage';
							}
						}
					});
				}
			});
		});
	});
	Core_uploader('#ads-purchase-image', {
		'serverScript': '/a/p=ads/f=fileUpload',
		'successHandler':function(file, data, response){
			updatePreview();
		}
	});
	Core_uploader('#ads-purchase-poster', {
		'serverScript': '/a/p=ads/f=posterUpload',
		'successHandler':function(file, data, response){
			updatePoster();
		}
	});
	function updatePreview() {
		if (chosenType==false) {
			return $('#ads-purchase-preview').addClass('disabled')
				.html('please choose an ad type and upload an image');
		}
		$.post('/a/p=ads/f=getTmpImage', function(ret) {
			if (!ret) {
				return $('#ads-purchase-preview').addClass('disabled').html('please upload an image');
			}
			$('#ads-purchase-preview')
				.removeClass()
				.html('<div style="border:1px solid red;width:'+chosenType.width+'px;height:'+chosenType.height+'px;"><img src="/a/f=getImg/w='+chosenType.width+'/h='+chosenType.height+'/'+ret+'"/></div><em>the red border is only to illustrate the size of the ad</em>');
		});
	}
	function updatePoster() {
/*		$.post('/a/p=ads/f=getTmpPoster', function(ret) {
			if (!ret) {
				return $('#ads-purchase-poster-preview')
					.addClass('disabled').html('please upload an image');
			} */
			$('#ads-purchase-poster-preview')
				.removeClass()
				.html('Poster uploaded');
//		});
	}
});
