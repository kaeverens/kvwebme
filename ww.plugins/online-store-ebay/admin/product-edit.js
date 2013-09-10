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
							+$('input[name="product_categories['+id+']"]').parent().text()
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
						if (!buyItNow) {
							alert('fill in the Buy It Now price');
							return false;
						}
						if (buyItNow<bidsStart*1.4) {
							alert('Buy Now Price should be at least '+(bidsStart*1.4));
							return false;
						}
						var id=$('input[name=id]').val();
						var quantity=+$('input[name="productsExtra[ebay_how_many_to_sell]"]').val();
						if (quantity<1) {
							quantity=1;
						}
						$.post(
							'/a/p=online-store-ebay/f=adminPublish',
							{
								'id':id,
								'bids_start_at':bidsStart,
								'buy_now_price':buyItNow,
								'quantity':quantity
							},
							function(ret) {
								console.log(ret);
								if (ret.errors && ret.errors.LongMessage) {
									ret.errors=[ret.errors];
								}
								if (ret.errors && ret.errors.length) {
									var errors=[];
									for (var i=0;i<ret.errors.length;++i) {
										errors.push(ret.errors[i].LongMessage);
									}
									alert(errors.join("\n"));
								}
								if (ret.reply.ItemID) {
									$.post(
										'/a/p=online-store-ebay/f=adminLinkProductToEbay',
										{
											'id':id,
											'ebay_id':ret.reply.ItemID
										},
										function() {
											alert('Successfully added');
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
