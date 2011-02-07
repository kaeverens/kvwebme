$(function(){
	$('div.products-product').each(function(){
		$('<a id="products-correction'
			+this.id.replace(/products-/, '')+'" '
			+'class="products-product-offer-correction" '
			+'href="javascript:;">correct the above entry</a>'
		)
			.click(function(){
				var id=this.id.replace(/products-correction/, '');
				var $wrapper=$('#products-'+id);
				if (this.products_correction_added) {
					$wrapper.find('.product-correction').remove();
					this.products_correction_added=false;
					return;
				}
				this.products_correction_added=true;
				$('<a href="javascript:;" class="product-correction">[correct]</a>')
					.click(function(){
						var $next=$(this).next(),
							name=$next[0].className.replace(/product-field /, ''),
							pid=$(this).closest('.products-product')[0].id
								.replace(/products-/, '');
						$('<div id="product-correction-dialog"><strong>'+name+'</strong><br /><table>'
							+'<tr><th>Email Address</th><td><input type="email" /></td></tr>'
							+'<tr><th>Current Value</th><td>'+$next.text()+'</td></tr>'
							+'<tr><th>Correction</th><th><textarea>'+$next.text()
							+'</textarea></td></tr>'
							+'</table>'
							+'</div>')
							.dialog({
								modal:true,
								buttons:{
									'Submit Correction':function(){
										var email=$('#product-correction-dialog input').val(),
											correction=$('#product-correction-dialog textarea').val();
										$.post(
											'/ww.plugins/products/frontend/visitor-corrections.php',
											{
												"email":email,
												"correction":correction,
												"pid":pid,
												"field":name
											},
											function(ret){
												if (ret.error) {
													return alert(ret.error);
												}
												alert('thank you');
												$('#product-correction-dialog').remove();
											},
											'json'
										);
									}
								},
								close:function(){
									$(this).remove();
								}
							});
					})
					.insertBefore('#products-'+id+' .product-field');
			})
			.insertAfter(this);
	})
});
