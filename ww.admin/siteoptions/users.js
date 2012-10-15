/*global google,htmlspecialchars*/
function Core_siteOptions_mapinit() {
	$('#user-location a').click();
}
$(function(){
	// { main details
	var active=$('select[name=active]').val();
	$('<a href="javascript:;" style="float:right;text-decoration:none"'+
		' title="add a new group">[+]</a>')
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
		var name=$('input[name=name]').val(),email=$('input[name=email]').val();
		msg='Dear '+name+',<br/>\n<br/>\nWe have activated your account.'+
			'<br/>\n<br/>\nYou can log in using your email address "'+email+'"'+
			' and the password you chose when registering.<br/>\n<br/>\nThank you.';
		$('<textarea name="email-to-send">'+msg+'</textarea>')
			.appendTo('#users-email-to-send-holder');
		$('#users-email-to-send')
			.css('display','table-row');
	});
	var $holder=$('#custom');
	var extras=$holder.find('input').val();
	$holder.empty();
	if (extras.length<3) {
		extras={};
	}
	else {
		extras=$.parseJSON(extras);
	}
	var html='<b>Insert custom data here</b><table style="width:100%">'+
		'<tr><th style="width:10%">Name</th><th>Value</th></tr>';
	var numextras=0;
	for (i in extras) {
		html+='<tr><th><input class="extras-name" name="extras['+numextras+']"'+
			' value="'+htmlspecialchars(i)+'" /></th>'+
			'<td><textarea name="extras_vals['+numextras+']" style="height:50px;">'+
			htmlspecialchars(extras[i])+'</textarea></td></tr>';
		numextras++;
	}
	html+='<tr><th><input class="extras-name" name="extras['+numextras+']" /></th>'+
		'<td><textarea style="height:50px;" name="extras_vals['+numextras+']">'+
		'</textarea></td></tr></table>';
	$holder.append(html);
	$('#custom input').live('change',function(){
		setTimeout(function(){
			numextras++;
			$holder.find('table').append(
				'<tr><th><input class="extras-name" name="extras['+numextras+']" /></th>'+
				'<td><textarea style="height:50px;" name="extras_vals['+numextras+']">'+
				'</textarea></td></tr></table>'
			);
		},1);
		$holder.find('input.extras-name').each(function(){
			if(this.value===''){
				if($(this).closest('tr').find('input')[1].value!=='') {
					return alert('field names cannot be empty!\nplease correct the empty field name before you save the data.');
				}
				$(this).closest('tr').remove();
			}
		});
	});
	// }
	// { locations
	// { lat/long
	$('#user-location a').click(function() {
		if (!window.google || !google.maps) {
			$('<script src="http://maps.googleapis.com/maps/api/js?sensor=false&callback=Core_siteOptions_mapinit"></script>')
				.appendTo(document.body);
			return;
		}
		$('<div id="siteoptions-map" style="width:800px;height:500px">loading...</div>')
			.dialog({
				'modal':'true',
				'close':function() {
					$('#siteoptions-map').remove();
					$(this).remove();
				},
				'width':800,
				'height':550,
				'buttons':{
					'Save':function() {
						var ctr=map.getCenter();
						$('input[name=location_lat]').val(ctr.lat());
						$('input[name=location_lng]').val(ctr.lng());
						$('#siteoptions-map').remove();
						$(this).remove();
					}
				}
			});
		var latlng=[
			$('input[name=location_lat]').val(),
			$('input[name=location_lng]').val()
		];
		var myOptions={
			zoom:8,
			center:new google.maps.LatLng(latlng[0], latlng[1]),
			mapTypeId:google.maps.MapTypeId.ROADMAP
		};
		var map=new google.maps.Map($('#siteoptions-map')[0], myOptions);
		var reticleImage=new google.maps.MarkerImage(
			'/i/reticle-32x32.png',
			new google.maps.Size(32,32),
			new google.maps.Point(0,0),
			new google.maps.Point(16,16)
		);
		var reticleShape={
			coords:[16,16,16,16],
			type:'rect'
		};
		var reticleMarker=new google.maps.Marker({
			position:map.getCenter(),
			map: map,
			icon: reticleImage,
			shape: reticleShape,
			optimized: false,
			zIndex:5
		});
		var addressWindow=new google.maps.InfoWindow();
		google.maps.event.addListener(map, 'bounds_changed', function(){
			reticleMarker.setPosition(map.getCenter());
			var geocoder=new google.maps.Geocoder();
			var ctr=map.getCenter();
			geocoder.geocode({
				'latLng': new google.maps.LatLng(ctr.lat(), ctr.lng())
			}, function(res) {
				addressWindow.close();
				if (res && res[1]) {
					addressWindow.setContent(res[1].formatted_address);
					addressWindow.open(map, reticleMarker);
				}
			});
		});
	});
	// }
	// { addresses
	$('#new-address').click(function(){
		$('<div id="new-dialog" title="New Address"></div>').dialog({
			modal:true,
			buttons:{
				'Save':function(){
					var name=$('input[name="add-name"]').val();
					var safe=name.replace(' ', '-');
          var street=$('input[name="add-street"]').val();
          var street2=$('input[name="add-street2"]').val();
          var postcode=$('input[name="add-postcode"]').val();
					var town=$('input[name="add-town"]').val();
          var county=$('input[name="add-county"]').val();
          var country=$('input[name="add-country"]').val();
					$('#add-content').append(
						'<table class="address-table"><tr>'+
						'<th colspan="2"><input type="radio" name="'+
						'default-address" value="'+safe+'"/>'+
						'<h3>'+name+'</h3>'+
						'<a href="javascript:;" class="delete-add">[-]</a></th>'+
						'<input type="hidden" name="address['+safe+']"/>'+
						'<tr><th>Street</th>'+
						'<td><input type="text" name="street-'+safe+'" value="'+street+'"/></td>'+
						'</tr><tr><th>Street 2</th>'+
						'<td><input type="text" name="street2-'+safe+'" value="'+street2+'"/></td>'+
						'</tr><tr><th>Postcode</th>'+
						'<td><input type="text" name="postcode-'+safe+'" value="'+postcode+'"/></td>'+
						'</tr><tr><th>Town</th>'+
						'<td><input type="text" name="town-'+safe+'" value="'+town+'"/></td>'+
						'</tr><tr><th>County</th>'+
						'<td><input type="text" name="county-'+safe+'" value="'+county+'"/></td>'+
						'</tr><tr><th>Country</th>'+
						'<td><input type="text" name="country-'+safe+'"'+
						'value="'+country+'"/></td>'+
						'</tr>'+
						'<th></tr></table>'
					);
					$(this).dialog('close').remove();
				},
				'Cancel':function(){
					$(this).dialog('close').remove();
				}
			}
		});
		$('#new-dialog').html(
			'<table>'+
			'<tr><th>Name</th><td><input type="text" name="add-name" value=""/></td></tr>'+
			'<tr><th>Street</th><td><input type="text" name="add-street"/></td></tr>'+
			'<tr><th>Street 2</th><td><input type="text" name="add-street2"/></td></tr>'+
			'<tr><th>Postcode</th><td><input type="text" name="add-postcode"/></td></tr>'+
			'<tr><th>Town</th><td><input type="text" name="add-town"/></td></tr>'+
			'<tr><th>County</th><td><input type="text" name="add-county"/></td></tr>'+
			'<tr><th>Country</th><td><input type="text" name="add-country" value=""/></td></tr>'+
			'</table>'
		);
	});
	$('.delete-add').live('click',function(){
		$(this).closest('.address-table').fadeOut('show').remove();
	});
	// }
	// }
	$('#tabs').tabs();
});
