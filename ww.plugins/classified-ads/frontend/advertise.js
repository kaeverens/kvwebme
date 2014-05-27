$(function() {
	$('.classifiedads-advertise-button').click(advertiseForm);
	var $wrapper=$('#classifiedads-wrapper');
	var adTypes={};
	function advertiseForm() {
		$.post(
			'/a/p=classified-ads/f=categoryTypesGet',
			function(ret) {
				if (!ret.length) {
					return alert('no prices! please contact an admin');
				}
				var html='<h2>Advertise in <i>'+classifiedads_categoryName+'</i></h2>';
				html+='<table class="classifiedads-advertise-form">'
					+'<tr><th>Ad type</th><td><select class="type"></select></td>'
					+'<th>Ad Cost</th><td class="cost">&nbsp;</td></tr>'
					+'<tr><th>How many days do you want to purchase?</th><td>'
					+'<input class="days" type="number"/></td>'
					+'<th>Minimum days</th><td class="minimum">&nbsp;</td></tr>'
					+'<tr><td colspan="4"><hr/></td></tr>'
					+'<tr><th>Your email address</th><td><input type="email" class="email"/></td><th>Your phone</th><td><input type="phone" class="phone"/></td></tr>'
					+'<tr><td colspan="4"><hr/></td></tr>'
					+'<tr class="title-row"><th>Ad Title<div class="title-desc"/></th><td colspan="3"><input class="title"/></td></tr>'
					+'<tr><th>Location</th><td><input class="location"/></td>'
					+'<th>Service/product cost</th><td><input class="service-cost"/></td></tr>'
					+'<tr class="description-row"><th>Ad Description<div class="description-desc"/></th><td colspan="3"><textarea class="description"/></td></tr>'
					+'<tr class="images-row"><th>Images<div class="images-desc"/></th><td colspan="3"><span id="image-upload"/><div id="images-preview"/></td></tr>'
					+'<tr><td colspan="4"><hr/></td></tr>'
					+'<tr><th>&nbsp;</th><td class="paypal">&nbsp;</td></tr>'
					+'</table>';
				$wrapper.html(html);
				// { type
				var opts=['<option value="0"> -- please choose -- </option>'];
				for (var i=0;i<ret.length;++i) {
					opts.push('<option value="'+ret[i].id+'">'+ret[i].name+'</option>');
					adTypes[ret[i].id]=ret[i];
				}
				$wrapper.find('.title').bind('change keyup', function() {
					var $this=$(this), val=$this.val(), max=60;
					if (val.length>40) {
						$wrapper.find('.title-desc').text('Max 60 characters');
					}
					if (val.length>max) {
						$this.val(val.substring(0, 59));
					}
				});
				$wrapper.find('.description').bind('change keyup', function() {
					var $this=$(this), val=$this.val(), max=+$this.attr('maxlength');
					if (val.length>max*.8) {
						$wrapper.find('.description-desc').text('Max '+max+' characters');
					}
					if (val.length>max) {
						$this.val(val.substring(0, max-1));
					}
				});
				var ad;
				$wrapper.find('.type').html(opts.join('')).change(function() {
					var $this=$(this);
					$this.find('[value=0]').remove();
					ad=adTypes[$this.val()];
					if (!ad.minimum_number_of_days) {
						ad.minimum_number_of_days=1;
					}
					var $days=$wrapper.find('.days');
					if (+$days.val()<ad.minimum_number_of_days) {
						$days.val(ad.minimum_number_of_days);
					}
					$wrapper.find('.minimum').html(ad.minimum_number_of_days);
					$wrapper.find('.description').attr('maxlength', ad.maxchars).change();
					function calcPrice() {
						if (+$days.val()<+ad.minimum_number_of_days) {
							$days.val(ad.minimum_number_of_days);
						}
						var price=ad.price_per_day*$days.val();
						$wrapper.find('.cost').html('€'+price.toFixed(2));
						site_url=document.location.toString()
							.replace(/(https?:\/\/[^\/]*).*/, '$1');
						// { paypal form
						var paypal='<form method="post" action="https://www.pay'
							+'pal.com/cgi-bin/webscr"><input type="hidden" value="_xclick" name="cmd"'
							+'/><input type="hidden" value="'+classifiedads_paypal+'" name="business"/>'
							+'<input type="hidden" value="Ads Purchase" name="item_name"/>'
							+'<input type="hidden" id="paypal-order-id" value="" name="item_number"/>'
							+'<input type="hidden" value="'+price+'" name="amount"/>'
							+'<input type="hidden" value="EUR" name="currency_code"/>'
							+'<input type="hidden" value="1" name="no_shipping"/>'
							+'<input type="hidden" value="1" name="no_note"/>'
							+'<input type="hidden" name="return" value="'+site_url+'" />'
							+'<input type="hidden" value="'+site_url
							+'/ww.plugins/classified-ads/verify/paypal.php" name="notify_url"/>'
							+'<input type="hidden" value="IC_Sample" name="bn"/><input type="image" a'
							+'lt="Make payments with payPal - it\'s fast, free and secure!" name="sub'
							+'mit" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="'
							+'width:68px;height:23px;"/><img w'
							+'idth="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" '
							+'alt=""/></form>';
						// }
						if (price==0) {
							paypal='<span><button>FREE</button></span>';
						}
						var $paypal=$(paypal)
							.appendTo($wrapper.find('.paypal').empty());
						$paypal.find('>button').button();
						$paypal.find('*').click(function() {
							$.post('/a/p=classified-ads/f=makePurchaseOrder', {
								'type_id':ad.id,
								'category_id':classifiedads_categoryId,
								'days':$days.val(),
								'title':$wrapper.find('.title').val(),
								'description':$wrapper.find('.description').val(),
								'email':$wrapper.find('.email').val(),
								'location':$wrapper.find('.location').val(),
								'cost':$wrapper.find('.service-cost').val(),
								'phone':$wrapper.find('.phone').val()
							}, function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								$('#paypal-order-id').val(ret.id).closest('form').submit();
								if (price==0) {
									$('#classifiedads-wrapper').html('<p>Thank you. Your classified ad is in the database now.</p>');
								}
							});
							return false;
						});
					}
					$wrapper.find('.days').change(calcPrice);
					$wrapper.find('.images-desc')
						.text('Images allowed: '+ad.number_of_images);
					$('.classifiedads-advertise-form .email').change();
					calcPrice();
					$('.images-row')
						.css('display', 'none');
					$wrapper.find('.images-row')
						.css('display', +ad.number_of_images?'table-row':'none');
				});
				// }
				function updatePreview() {
					$.post('/a/p=classified-ads/f=advertiseThumbsGet', function(ret) {
						var images=[];
						for (var i=0;i<ret.images.length;++i) {
							if (i<+ad.number_of_images) {
								images.push(
									'<div><img src="/a/f=getImg/w=120/h=120/'+ret.dir+'/'
									+ret.images[i]+'"/><br/><a href="#">[delete]</a></div>'
								);
							}
							else {
								$.post(
									'/a/p=classified-ads/f=advertiseFileDelete',
									{
										'file':ret.images[i]
									}
								);
							}
						}
						$wrapper.find('#images-preview')
							.html(images.join(''))
							.find('a').click(function() {
								var fname=$(this).siblings('img').attr('src')
									.replace(/.*\//, '');
								$.post(
									'/a/p=classified-ads/f=advertiseFileDelete',
									{
										'file':fname
									},
									updatePreview
								);
								return false;
							});
					});
				}
				Core_uploader('#image-upload', {
					'serverScript': '/a/p=classified-ads/f=fileUpload',
					'successHandler':function(file, data, response){
						updatePreview();
					}
				});
				$('.classifiedads-advertise-form .email')
					.change(function() {
						var val=$(this).val();
						if (!val) {
							return;
						}
						$.post(
							'/a/f=userGetUid',
							{
								'email':val
							},
							function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								if (ret.uid && !(/tmp-/.test(ret.uid))) {
									if (userdata.id && userdata.id==ret.uid) {
										return;
									}
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
							}
						);
					})
					.change();
				$('.images-row')
					.css('display', 'none');
			}
		);
	}
});
