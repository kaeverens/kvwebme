function compare(obj1, obj2) {
	function size(obj) {
		var size = 0;
		for (var keyName in obj) {
			if (keyName != null) {
				size++;
			}
		}
		return size;
	}

	if (size(obj1) != size(obj2)) {
		return false;
	}

	for(var keyName in obj1) {
		var value1 = obj1[keyName];
		var value2 = obj2[keyName];

		if (typeof value1 != typeof value2) {
			return false;
		}

		// For jQuery objects:
		if (value1 && value1.length && (value1[0] !== undefined && value1[0].tagName)) {
			if(!value2 || value2.length != value1.length || !value2[0].tagName || value2[0].tagName != value1[0].tagName) {
				return false;
			}
		}
		else if (typeof value1 == 'function' || typeof value1 == 'object') {
			var equal = compare(value1, value2);
			if (!equal) {
				return equal;
			}
		}
		else if (value1 != value2) {
			return false;
		}
	}
	return true;
}
function findMatchingAddress(address){
	if (userdata.id==null) {
		return true;
	}
	for(var i in userdata.address){
		if (userdata.address[i].default) {
			address.default=userdata.address[i].default;
		}
		if (compare(userdata.address[i],address)) {
			return true;
		}
	}
	return false;
}
function populate_delivery(address,bill){
	if (userdata.id==null) {
		return;
	}
	for(var i in userdata.address){
		if(userdata.address[i].default=="yes"&&address==null){
			var current=userdata.address[i];
			break;
		}
		if(i==address){
			var current=userdata.address[i];
			break;
		}
	}
	if(current!=null){
		$('input[name="'+bill+'Street"]').val(current.street);
		$('input[name="'+bill+'Street2"]').val(current.street2);
		$('input[name="'+bill+'Town"]').val(current.town);
		$('input[name="'+bill+'County"]').val(current.county);
		$('input[name="'+bill+'Country"]').val(current.country);
	}
}
$(function(){
	// { populate delivery details
	if(userdata.id!=null){
		$.get('/a/f=getUserData',
			function(user){
				$.extend(userdata,user);		
				var components=userdata.name.split(' ');
				var firstname=components.shift();
				var lastname=components.join(' ');
				$('input[name="FirstName"],input[name="Billing_FirstName"]').val(firstname);
				$('input[name="Surname"],input[name="Billing_Surname"]').val(lastname);
				$('input[name="Phone"],input[name="Billing_Phone"]').val(userdata.phone);
				$('input[name="Email"],input[name="Billing_Email"]').val(userdata.email);
				populate_delivery(null,'');
				var html='<tr><td>Address</td><td><select name="address">';
				for(var i in userdata.address){
					var def=(userdata.address[i].default=='yes')?' selected="selected"':'';
					html+='<option'+def+' value="'+i+'">'+i.replace('-',' ')+'</option>';
				}	
				html+='</select></td></tr>';
				$('.shoppingcartCheckout tr:first').before(html);
				$('.shoppingcartCheckout_billing tr:first').before(html);
				$('.shoppingcartCheckout_billing select[name="address"]').addClass('billing');

				populate_delivery(null,'Billing_');
			},
			'json'
		);
		// }
		$('input[name="action"]').click(function(){
			var address={		
				street:$('input[name="Street"]').val(),
				street2:$('input[name="Street2"]').val(),
				town:$('input[name="Town"]').val(),
				county:$('input[name="County"]').val(),
				country:$('input[name="Country"]').val(),
			};
			var result=findMatchingAddress(address);
			if(result==false){
				var result=confirm('You have created a new address, would you like to save this address?');
				if(result==true){
					var name=prompt('Please a name to describe this address:');
					$('.shoppingcartCheckout').append('<input type="hidden" name="save-address" value="'+name+'"/>');
				}
			}
		});
		$('select[name="address"]').live('change',function(){
			var bill=($(this).hasClass('billing'))?'Billing_':'';
			populate_delivery($(this).val(),bill);
		});
	}
	switch(+os_post_vars._viewtype) {
		case 2: // { 5-step
			// { list of panels
			var tabs=[];
			tabs.push('Login');
			tabs.push('Delivery Address');
			tabs.push('Billing Address');
			if (os_post_vars._pandp) {
				tabs.push('Delivery Options');
			}
			tabs.push('Payment');
			// }
			var html='<div id="online-store-checkout-accordion-wrapper">'
			// { panels
			for (var i=0;i<tabs.length;++i) {
				html+='<h2 panel="'+tabs[i]+'" class="__" lang-context="core">'
					+'<a href="#">'+tabs[i]+'</a></h2>'
					+'<div>&nbsp;</div>';
			}
			// }
			html+='</div>';
			// { add form
			var inps=[
				'Billing_Country', 'Billing_County', 'Billing_Email',
				'Billing_FirstName', 'Billing_Phone', 'Billing_Postcode',
				'Billing_Street', 'Billing_Street2', 'Billing_Surname',
				'Billing_Town', 'Country', 'County', 'Email', 'FirstName', 'Phone',
				'Postcode', 'Street', 'Street2', 'Surname', 'Town',
				'_payment_method_type', 'action', 'os_no_submit'
			];
			html+='<form id="online-store-form" method="post" action="'
				+document.location.toString().replace(/\?.*/, '')
				+'">'
				+'<input type="hidden" name="'
				+inps.join('"/><input type="hidden" name="', inps)
				+'"/></form>';
			// }
			$(html).appendTo('#online-store-wrapper');
			$('input[name=os_no_submit]').val(1);
			function showStep(ev, ui) {
				if (!ui.newHeader) {
					ui.newHeader=$('#online-store-wrapper>div>h2:first-child');
					ui.newContent=$('#online-store-wrapper>div>div:nth-child(2)');
				}
				var content=ui.newContent;
				var panel=ui.newHeader.attr('panel');
				switch(panel) {
					case 'Login': // {
						if (userdata.id) {
							content.text('logged in as '+userdata.name);
							setTimeout(function() {
								$('#online-store-checkout-accordion-wrapper').accordion(
									'activate',
									'h2[panel="Delivery Address"]'
								);
							}, 500);
							return;
						}
						var table='<table style="width:100%"><tr>'
							// { login
							+'<td class="user-login">'
							+'<label class="email">'
							+'<span class="__" lang-context="core">Email</span>'
							+'<input type="email"/>'
							+'</label>'
							+'<label class="password">'
							+'<span class="__" lang-context="core">Password</span>'
							+'<input type="password"/>'
							+'</label>'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Login</button>'
							+'</label>'
							+'</td>'
							// }
							// { or
							+'<td class="__ or" lang-context="core">or</td>'
							// }
							// { register
							+'<td class="user-register">'
							+'<label class="email">'
							+'<span class="__" lang-context="core">Email</span>'
							+'<input type="email"/>'
							+'</label>'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Register</button>'
							+'</label>'
							+'</td>'
							// }
							// { or
							+'<td class="__ or" lang-context="core">or</td>'
							// }
							// { checkout as guest
							+'<td class="user-guest">'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Checkout as guest'
							+'</button></label></td>'
							// }
							+'</tr></table>';
						content.html(table);
						var fnSubmit=function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							$('#online-store-form').submit();
						}
						content.find('.user-login button').click(function() {
							var $form=$('#online-store-wrapper .user-login');
							var email=$form.find('.email input').val();
							var passw=$form.find('.password input').val();
							$.post('/a/f=login', {
								'email':email,
								'password':passw
							}, fnSubmit, 'json');
						});
						content.find('.user-register button').click(function() {
							var $form=$('#online-store-wrapper .user-register');
							var email=$form.find('.email input').val();
							$.post('/a/f=sendRegistrationToken', {
								'email':email
							}, function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								var $dialog=$('<div class="online-store">'
									+'<h2 class="__" lang-context="core">'
									+'Verify your email address</h2>'
									+'<p class="__" lang-context="core">We have send a 5-digit '
									+'token to your email address to verify it. Please check '
									+'your email, then enter the token below. Then enter a '
									+'password you want to use for this site.</p>'
									+'<label class="token"><span class="__" lang-context="core">'
									+'Token</span><input id="form-token"/></label>'
									+'<label class="password"><span class="__" '
									+'lang-context="core">Password</span>'
									+'<input type="password" id="form-pass1"/></label>'
									+'<label class="repeat-password"><span class="__" '
									+'lang-context="core">Repeat Password</span>'
									+'<input type="password" id="form-pass2"/></label>'
									+'</div>'
								).dialog({
									'modal': true,
									'width': 350,
									'close':function() {
										$dialog.remove();
									},
									'buttons':{
										'Register':function() {
											var token=$('#form-token').val(),
												pass1=$('#form-pass1').val(),
												pass2=$('#form-pass2').val();
											if (token.length!=5) {
												return alert('Token must be 5 digits in length');
											}
											if (!pass1 || pass1!=pass2) {
												return alert('Passwords must be equal');
											}
											$.post('/a/p=privacy/f=register', {
												'token':token,
												'password':pass1
											}, function() {
												if (ret.error) {
													return alert(ret.error);
												}
												alert('Thank you. We are logging you in now.');
												$.post('/a/f=login', {
													'email':email,
													'password':pass1
												}, fnSubmit);
											});
										}
									}
								});
							}, 'json');
						});
						content.find('.user-guest button').click(function() {
							$('#online-store-checkout-accordion-wrapper').accordion(
								'activate',
								'h2[panel="Delivery Address"]'
							);
						});
					break; // }
				}
			}
			var $accordion=$('#online-store-checkout-accordion-wrapper').accordion({
				'autoHeight':false,
				'create':showStep,
				'changestart':showStep
			});
		break; // }
		default: // {
			$('input[name=os_voucher]').change(function() {
				var $this=$(this);
				var code=$this.val();
				if (!code) {
					return;
				}
				var email=$('#ww-pagecontent input[name=Email]').val();
				$.post('/a/p=online-store/f=checkVoucher', {
					"email": email,
					"code" : code
				}, function(ret) {
					if (ret.error) {
						$('<em>'+ret.error+'</em>').dialog({
							"modal": true
						});
						$this.val('');
						return;
					}
					$('<input type="hidden" name="os_no_submit" value="1"/>')
						.insertAfter($this);
					$this.closest('form').submit();
				}, 'json');
			});
			$('select[name=Country]').change(function() {
				var $this=$(this);
				$('<input type="hidden" name="os_no_submit" value="1"/>')
					.insertAfter($this);
				$this.closest('form').submit();
			});
		// }
	}
	if (os_post_vars) {
		for (var i in os_post_vars) {
			$('input[name="'+i+'"],select[name="'+i+'"],textarea[name="'+i+'"]')
				.val(os_post_vars[i]);
		}
	}
});
