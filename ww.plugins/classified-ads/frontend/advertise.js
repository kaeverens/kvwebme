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
					+'<th>Minimum days</th><td class="minimum">&nbsp;</td></tr>'
					+'<tr><th>How many days do you want to purchase?</th><td>'
					+'<input class="days" type="number"/></td>'
					+'<th>Maximum length</th><td class="max-length">&nbsp;</td></tr>'
					+'<tr><th>Cost</th><td class="cost">&nbsp;</td>'
					+'<th>&nbsp;</th><td class="pay">&nbsp;</td></tr>'
					+'</table>';
				$wrapper.html(html);
				// { type
				var opts=['<option value="0"> -- please choose -- </option>'];
				for (var i=0;i<ret.length;++i) {
					opts.push('<option value="'+ret[i].id+'">'+ret[i].name+'</option>');
					adTypes[ret[i].id]=ret[i];
				}
				$wrapper.find('.type').html(opts.join('')).change(function() {
					var $this=$(this);
					$this.find('[value=0]').remove();
					var ad=adTypes[$this.val()];
					if (!ad.minimum_number_of_days) {
						ad.minimum_number_of_days=1;
					}
					var $days=$wrapper.find('.days');
					if (+$days.val()<ad.minimum_number_of_days) {
						$days.val(ad.minimum_number_of_days);
					}
					$wrapper.find('.minimum').html(ad.minimum_number_of_days);
					$wrapper.find('.max-length').html(ad.maxchars+' letters');
					function calcPrice() {
						if (+$days.val()<+ad.minimum_number_of_days) {
							$days.val(ad.minimum_number_of_days);
						}
						var price=ad.price_per_day*$days.val();
						$wrapper.find('.cost').html('€'+price.toPrecision(3));
					}
					$wrapper.find('.days').change(calcPrice);
					calcPrice();
				});
				// }
			}
		);
	}
});
