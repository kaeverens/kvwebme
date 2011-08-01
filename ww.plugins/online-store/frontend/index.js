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
		$.get(
			'/ww.incs/get-user-data.php?id='+userdata.id,
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
	if (os_post_vars) {
		for (var i in os_post_vars) {
			$('input[name="'+i+'"],select[name="'+i+'"],textarea[name="'+i+'"]')
				.val(os_post_vars[i]);
		}
	}
});
