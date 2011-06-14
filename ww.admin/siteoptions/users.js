$(function(){
	function randchar(arg) {
		return arg[Math.floor(Math.random() * arg.length)];
	}
	function makepass() {
		var consts = ['b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z'],
			hard_consts = ['b','c','d','f','g','h','k','m','p','s','t','v','z'],
			link_consts = ['h','l','r'],
			vowels = ['a','e','i','o','u'],
			digits = ['1','2','3','4','5','6','7','8','9'];
		return randchar(hard_consts) + randchar(link_consts) + randchar(vowels) + randchar(consts) + randchar(digits) + randchar(hard_consts) + randchar(vowels) + randchar(consts);
	}

	var active=$('select[name=active]').val();
	$('<a href="javascript:;" style="float:right;text-decoration:none" title="add a new group">[+]</a>')
		.click(function(){
			$('<input name="new_groups[]" />').appendTo('.groups');
		})
		.prependTo('.groups');
	$('select[name=active],input[name=password],input[name=email]').change(function(){
		var val=+$('select[name=active]').val(),msg;
		$('#users-email-to-send-holder').empty();
		$('#users-email-to-send').css('display','none');
		if(val==active){
			return;
		}
		var name=$('input[name=name]').val(),email=$('input[name=email]').val(),password=$('input[name=password]').val();
		msg='Dear '+name+',\n\nWe have activated your account.\n\nYou can log in using your email address "'+email+'" and the password you chose when registering.\n\nThank you.'
		$('<textarea name="email-to-send">'+msg+'</textarea>')
			.appendTo('#users-email-to-send-holder');
		$('#users-email-to-send')
			.css('display','table-row');
	});
	var $holder=$('#extras-wrapper');
	var extras=$holder.find('input').val();
	$holder.empty();
	if (extras.length<3) {
		extras={};
	}
	else {
		extras=$.parseJSON(extras);
	}
	var html='<b>Insert custom data here</b><table><tr><th>Name</th><th>Value</th></tr>';
	var numextras=0;
	for (i in extras) {
		html+='<tr><th><input class="extras-name" name="extras['+numextras+']" value="'+htmlspecialchars(i)+'" /></th>'
			+'<td><input name="extras_vals['+numextras+']" value="'+htmlspecialchars(extras[i])+'" /></td></tr>';
		numextras++;
	}
	html+='<tr><th><input class="extras-name" name="extras['+numextras+']" /></th><td><input name="extras_vals['+numextras+']" /></td></tr></table>';
	$holder.append(html);
	$('#extras-wrapper input').live('change',function(){
		setTimeout(function(){
			numextras++;
			$holder.find('table').append(
				'<tr><th><input class="extras-name" name="extras['+numextras+']" /></th><td><input name="extras_vals['+numextras+']" /></td></tr></table>'
			);
		},1);
		$holder.find('input.extras-name').each(function(){
			if(this.value==''){
				if($(this).closest('tr').find('input')[1].value!='') {
					return alert("field names cannot be empty!\nplease correct the empty field name before you save the data.");
				}
				$(this).closest('tr').remove();
			}
		});
	});
	$('#tabs').tabs();
	$('#new-address').click(function(){
		$('<div id="new-dialog" title="New Address"></div>').dialog({
			modal:true,
			buttons:{
				'Save':function(){
					var name=$('input[name="add-name"]').val();
					var safe=name.replace(" ","-");
          var street=$('input[name="add-street"]').val();
          var street2=$('input[name="add-street2"]').val();
          var town=$('input[name="add-town"]').val();
          var county=$('input[name="add-county"]').val();
          var country=$('input[name="add-country"]').val();
					$('#add-content').append(
						'<table class="address-table"><tr>'
							+ '<th colspan="2"><input type="radio" name="'
							+ 'default-address" value="'+safe+'"/>'
							+ '<h3>'+name+'</h3>'
							+ '<a href="javascript:;" class="delete-add">[-]</a></th>'
							+ '<input type="hidden" name="address['+safe+']"/>'
							+ '<tr><th>Street</th>'
							+ '<td><input type="text" name="street-'+safe+'" value="'+street+'"/></td>'
							+ '</tr><tr><th>Street 2</th>'
							+ '<td><input type="text" name="street2-'+safe+'" value="'+street2+'"/></td>'
							+ '</tr><tr><th>Town</th>'
							+ '<td><input type="text" name="town-'+safe+'" value="'+town+'"/></td>'
							+ '</tr><tr><th>County</th>'
							+ '<td><input type="text" name="county-'+safe+'" value="'+county+'"/></td>'
							+ '</tr><tr><th>Country</th>'
							+ '<td><input type="text" name="country-'+safe+'"'
							+ 'value="'+country+'"/></td>'
							+ '</tr>'
							+ '<th></tr></table>'
					);
					$(this).dialog('close').remove();
				},
				'Cancel':function(){
					$(this).dialog('close').remove();
				}
			}
		});
		$('#new-dialog').html(
			'<table>'
			+ '<tr>'
				+ '<th>Name</th>'
				+ '<td><input type="text" name="add-name" value=""/></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>Street</th>'
				+ '<td><input type="text" name="add-street"/></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>Street 2</th>'
				+ '<td><input type="text" name="add-street2"/></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>Town</th>'
				+ '<td><input type="text" name="add-town"/></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>County</th>'
				+ '<td><input type="text" name="add-county"/></td>'
			+ '</tr>'
			+ '<tr>'
				+ '<th>Country</th>'
				+ '<td><input type="text" name="add-country" value=""/></td>'
			+ '</tr>'
			+ '</table>'
		);
	});
	$('.delete-add').live('click',function(){
		$(this).closest('.address-table').fadeOut('show').remove();
	});
});
