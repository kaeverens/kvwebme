$(function(){
	$('#buy-credits').click(function(){
		$.post('/ww.plugins/site-credits/admin/get-credit-details.php',function(ret){
			$('<table>'
				+'<tr><td>Credits to buy</td><td><input id="num-creds" value="10" /></td></tr>'
				+'<tr><td>Price per credit</td><td>'+ret['currency-symbol']
				+'<span id="cost-per-credit">'+ret['credit-costs'][0][1]+'</span></td></tr>'
				+'<tr><td>Paypal Fee</td><td>&euro;<span id="paypal-fee"></span> (estimate)</td></tr>'
				+'<tr><td>Total cost</td><td>&euro;<span id="total-cost"></span></td></tr>'
				+'<tr><td></td><td id="pay-button">&nbsp;</td></tr>'
				+'</table>'
			).dialog({
				modal:true,
				close:function(){
					$(this).remove();
				}
			});
			function update_paypal_button(){
				var num_credits=+$('#num-creds').val();
				var costs=ret['credit-costs'];
				var ppc=costs[0][1];
				for (var i=0;i<costs.length;++i) {
					if (costs[i][0]<=num_credits) {
						$('#cost-per-credit').text(costs[i][1]);
						ppc=costs[i][1];
					}
				}
				var cost=num_credits*ppc;
				var pp_fee=Math.ceil((cost*.039+.35)*100)/100;
				$('#paypal-fee').text(pp_fee);
				cost+=pp_fee;
				$('#total-cost').text(cost);
				$('#pay-button').html(
					'<form id="online-store-paypal" method="post" action="https://www.paypal.com/cgi-bin/webscr">'
					+'<input type="hidden" value="_xclick" name="cmd"/>'
					+'<input type="hidden" value="sales@kvsites.ie" name="business"/>'
					+'<input type="hidden" value="Purchase of credits from KV Sites" name="item_name"/>'
					+'<input type="hidden" value="'+num_credits+'" name="item_number"/>'
					+'<input type="hidden" value="'+cost+'" name="amount"/>'
					+'<input type="hidden" value="'+ret['currency']+'" name="currency_code"/>'
					+'<input type="hidden" value="1" name="no_shipping"/>'
					+'<input type="hidden" value="1" name="no_note"/>'
					+'<input type="hidden" name="return" value="'
					+document.location.toString()+'" />'
					+'<input type="hidden" value="'
					+document.location.toString().replace(
						/ww\.admin.*/,
						'ww.plugins/site-credits/verify-paypal.php'
					)
					+'" name="notify_url"/>'
					+'<input type="hidden" value="IC_Sample" name="bn"/>'
					+'<input type="image" alt="Make payments with payPal - it\'s fast, free and secure!" name="submit" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif"/>'
					+'<img width="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" alt="" />'
					+'</form>'
				);
			}
			update_paypal_button();
			$('#num-creds').keyup(update_paypal_button);
		});
	});
});
