$(function() {
	var $wrapper=$('#ebay-wrapper');
	var cats=[];
	$('#categories input:checked').each(function(k, v) {
		cats.push(+($(this).attr('name').replace(/[^0-9]/g, '')));
	});
	if (!cats.length) {
		$wrapper.html('<p>no categories selected. please set a category for this product, then Save and check again.</p>');
	}
	else {
		$.post(
			'/a/p=online-store-ebay/f=adminCheckEbayCats',
			{
				'cats':cats
			},
			function(ret) {
				if (ret.invalids.length) {
					var bits=['<p>One or more categories is not linked to eBay correctly. Please correct this, then Save the product and try again.</p><ul>'];
					for (var i=0;i<ret.invalids.length;++i) {
						var id=+ret.invalids[i];
						bits.push(
							'<li data-id="'+ret.invalids[i]+'"><a href="#">'
							+$('#category-name-'+id).text()
							+'</a></li>'
						);
					}
					$wrapper.html(bits.join('')+'</ul>');
					$wrapper.find('a').click(linkToEbayCategory);
					return;
				}
				$('<button>Publish in eBay</button>')
					.click(function() {
						var buyItNow=+$('input[name="productsExtra[ebay_buy_now_price]"]')
							.val();
						var bidsStart=+$('input[name="productsExtra[ebay_bids_start_at]"]')
							.val();
						if (!buyItNow || !bidsStart) {
							alert('fill in the Bids and Buy prices');
							return false;
						}
						if (buyItNow<bidsStart*1.4) {
							alert('Buy Now Price should be at least '+(bidsStart*1.4));
							return false;
						}
						var id=$('input[name=id]').val();
						$.post(
							'/a/p=online-store-ebay/f=adminPublish',
							{
								'id':id,
								'bids_start_at':$('input[name="productsExtra[ebay_bids_start_at]"]').val(),
								'buy_now_price':$('input[name="productsExtra[ebay_buy_now_price]"]').val()
							},
							function(ret) {
								if (ret.Errors && ret.Errors.length) {
									var errors=[];
									for (var i=0;i<ret.Errors.length;++i) {
										errors.push(ret.Errors[i].LongMessage);
									}
									alert(errors.join("\n"));
									console.log(errors);
								}
								console.log(ret);
								if (ret.ItemID) {
									$.post(
										'/a/p=online-store-ebay/f=adminLinkProductToEbay',
										{
											'id':id,
											'ebay_id':ret.ItemID
										},
										function() {
											alert('Successfully added');
											$('#products-form').submit();
										}
									);
								}
							}
						);
						return false;
					})
					.appendTo($wrapper);
			}
		);
	}
	function linkToEbayCategory() {
		var $this=$(this);
		var id=$this.parents('li').data('id');
		var $inp=$('<input type="hidden" name="linkToEbay'+id+'"/>')
			.insertAfter($this);
		$this.unbind('click');
		var opts={
			'empty_value': 'null',
			'indexed': true,
			'on_each_change':'/a/p=online-store-ebay/f=adminGetEbayCats',
			'choose':function(ret) {
				console.log(ret);
			}
		}
		$.post(
			'/a/p=online-store-ebay/f=adminGetEbayCats/id=-1',
			function(tree) {
				$inp.optionTree(tree, opts).change(function(ret) {
					$.post('/a/p=online-store-ebay/f=adminLinkEbayCat', {
						'id':id,
						'ebay_id':$(this).val()
					});
				});
			}
		);
		return false;
	}
});
