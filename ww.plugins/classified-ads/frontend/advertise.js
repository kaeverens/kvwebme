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
					+'<th>Cost</th><td class="cost">&nbsp;</td></tr>'
					+'<tr><th>How many days do you want to purchase?</th><td>'
					+'<input class="days" type="number"/></td>'
					+'<th>Minimum days</th><td class="minimum">&nbsp;</td></tr>'
					+'<tr><th>Title</th><td colspan="2"><input class="title"/></td><td class="title-desc">&nbsp;</td></tr>'
					+'<tr><th>Description</th><td colspan="2"><textarea class="description"/></td><td class="description-desc">&nbsp;</td></tr>'
					+'<tr class="images-row"><th>Images</th><td><span id="image-upload"/></td><td class="images-desc">&nbsp;</td></tr>'
					+'<th>&nbsp;</th><td class="pay">&nbsp;</td></tr>'
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
				$wrapper.find('.type').html(opts.join('')).change(function() {
					var $this=$(this);
					$this.find('[value=0]').remove();
					var ad=adTypes[$this.val()];
					console.log(ad);
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
						$wrapper.find('.cost').html('€'+price.toPrecision(3));
					}
					$wrapper.find('.days').change(calcPrice);
					calcPrice();
				});
				// }
				Core_uploader('#image-upload', {
					'serverScript': '/a/p=classified-ads/f=fileUpload',
					'successHandler':function(file, data, response){
						updatePreview();
					}
				});
			}
		);
	}
});
