$(function() {
	var $wrapper=$('#ads-purchase-wrapper');
	var types=[];
	var chosenType=false;
	var html='<table>'
		+'<tr><th>Ad Type</th><td><select id="ads-purchase-type_id"/></td></tr>'
		+'<tr><th>Price</th><td id="ads-purchase-price_per_day"></td></tr>'
		+'<tr><th>How many Days?</th><td><input type="number" style="width:30px" id="ads-purchase-days-wanted" value="7"/></td></tr>'
		+'<tr><th>Target URL</th><td><input id="ads-purchase-target_url" style="width:300px" value="http://yourwebsiteaddress/" /></tr>'
		+'<tr><th>Image Size Required</th><td id="ads-purchase-size"></td></tr>'
		+'<tr><th>Your Image</th><td><span id="ads-purchase-image"/></td></tr>'
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
					.html('please choose an ad type');
				updatePreview();
				return;
			}
			var days=$('#ads-purchase-days-wanted').val();
			var subtotal=days*chosenType.price_per_day;
			$('#ads-purchase-subtotal').html('€'+subtotal);
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
				+'<input type="hidden" value="EUR" name="currency_code"/><input type="hidden" value="1" name="no_shipping"/>'
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
			var $paypal=$(paypal).appendTo($('#ads-purchase-purchase').empty());
			$paypal.find('input').click(function() {
				$.post('/a/p=ads/f=makePurchaseOrder', {
					'type_id':$('#ads-purchase-type_id').val(),
					'days':$('#ads-purchase-days-wanted').val(),
					'target_url':$('#ads-purchase-target_url').val()
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
						.html('please choose an ad type');
					chosenType=false;
					$('#ads-purchase-days-wanted').change();
					updatePreview();
					return;
				}
				chosenType=types[id];
				$('#ads-purchase-price_per_day').html('€'+chosenType.price_per_day+' per day');
				$('#ads-purchase-size').html(chosenType.width+'px x '+chosenType.height+'px');
				$('#ads-purchase-days-wanted').change();
				updatePreview();
			})
			.change();
	});
	Core_uploader('#ads-purchase-image', {
		'serverScript': '/a/p=ads/f=fileUpload',
		'successHandler':function(file, data, response){
			updatePreview();
		}
	});
	function updatePreview() {
		if (chosenType==false) {
			return $('#ads-purchase-preview').html('please choose an ad type');
		}
		$.post('/a/p=ads/f=getTmpImage', function(ret) {
			if (!ret) {
				return $('#ads-purchase-preview').html('please upload an image');
			}
			$('#ads-purchase-preview').html('<div style="border:1px solid red;width:'+chosenType.width+'px;height:'+chosenType.height+'px;"><img src="/a/f=getImg/w='+chosenType.width+'/h='+chosenType.height+'/'+ret+'"/></div><em>the red border is only to illustrate the size of the ad</em>');
		});
	}
});
