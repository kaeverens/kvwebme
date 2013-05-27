function showAvatar() {
  var avatar=$('#avatar-wrapper');
  var uid = avatar.data('uid');

  id=[];
  id.push(uid);

  $.get('/a/f=usersAvatarsGet', {
    ids:id
  }, function(ret){
    var src='';
    if(ret[0]){
      src='/f' + ret[0].avatar
    }
    else{
      src= '/i/silhouette-256x256.png';
    }
					
    $('#avatar-wrapper')
      .append('<img id="avatar" style="width:64px;height:64px;display:inline-block;cursor:pointer;"'+
              'src="' + src + '" />')
      .click(function(){
      var uid = $('#avatar-wrapper').data('uid');
      var avatarSrc = src ? src : ''; 			
      var avatarEdit='<br/><table><tr><th>Image</th>'
                    +'<input class="saorfm user-avatar"/></td></tr></table>';
      
      var $dialog=$('<div>'+
                      '<h1>' + userdata.name + '</h1>'+							 
                      '<img style="width:256px;height:256px;"'+
                      'src="' + src  + '" class="avatarDialogWindow" />'+
                       avatarEdit +
                    '</div>')
                 .dialog({'modal':true,
                          'close':function(){
                                  $dialog.remove();
                                  }
                           });					

      var filename=avatarSrc.split("/");
      filename=filename[filename.length-1];

      $dialog.find('.user-avatar')
             .val(filename)
             .change(function(){
               var $this = $(this);
               src = $this.val();
               $.post('/a/f=userSetAvatar', {'src':src});
               $('.avatarDialogWindow').attr('src','/f'+src);
               $('#avatar').attr('src', '/f'+src);
               src='/f'+src;
               filename = $this.val().split('/');
               filename = filename[filename.length-1];
               $this.val(filename);               
             })
             .saorfm({
               'rpc' : '/ww.incs/saorfm/rpc.php',
               'select': 'file',
               'prefix': userdata.isAdmin? '' : '/users/' + userdata.id
               });
      });
  }, 'json');
}

function edit_user_dialog( id ){
	$( "<div id='users-dialog' title='Edit User Details'></div>" )
	.html( "Loading..." )
	.dialog({
		modal : true,
		buttons : {
			"Save" : function( ){
				var name = $( "input[name='user-name']" ).val( );
				if( name == "" ){
					$( "#error" ).html( "the name field is required" );
					return false;
				}
				var phone = $( "input[name='user-phone']" ).val( );
				$.post("/ww.plugins/privacy/frontend/save_user_info.php",
					{ "name" : name, "phone" : phone }	
				);
				location.reload( true );
			},
			"Cancel" : function( ){
				$( "#users-dialog" ).dialog( "close" ).remove( );
			}
		}
	});
	$.get("/ww.plugins/privacy/frontend/edit_user_info.php",
		function( html ){
			$( "#users-dialog" ).html( html );
		}
	);
}
function edit_address(id){
	$( "<div id='users-dialog' title='Edit Address'></div>" )
	.html( "Loading..." )
	.dialog({
		modal : true,
		buttons : {
			"Save" : function( ){
	  var name=$('input[name="add-name"]').val();
	  var street=$('input[name="add-street"]').val();
	  var street2=$('input[name="add-street2"]').val();
	  var town=$('input[name="add-town"]').val();
	  var county=$('input[name="add-county"]').val();
	  var country=$('input[name="add-country"]').val();
				$.post("/ww.plugins/privacy/frontend/save_user_info.php?action=update",
					{
						"name" : name,
						"street" : street,
						"street2" : street2,
						"town" : town,
						"county" : county,
						"country" : country,
					}	
				);
				userdata.address[name]={
					"name" : name,
					"street" : street,
					"street2" : street2,
					"town" : town,
					"county" : county,
					"country" : country
				};
				$( "#users-dialog" ).dialog( "close" ).remove( );
			},
			"Cancel" : function( ){
				$( "#users-dialog" ).dialog( "close" ).remove( );
			}
		}
	});

	street=userdata.address[id].street;
	street2=userdata.address[id].street2;
	town=userdata.address[id].town;
	county=userdata.address[id].county;
	country=userdata.address[id].country;

	$("#users-dialog").html(
	  '<table>'
	+ '<input type="hidden" name="add-name" value="'+id+'"/>'
	  + '<tr>'
+ '<th>Street</th>'
	 + '<td><input type="text" name="add-street" value="'+street+'"/></td>'
	  + '</tr>'
	  + '<tr>'
+ '<th>Street 2</th>'
	+ '<td><input type="text" name="add-street2" value="'+street2+'"/></td>'
	  + '</tr>'
	  + '<tr>'
+ '<th>Town</th>'
+ '<td><input type="text" name="add-town" value="'+town+'"/></td>'
	  + '</tr>'
	  + '<tr>'
+ '<th>County</th>'
	  + '<td><input type="text" name="add-county" value="'+county+'"/></td>'
	  + '</tr>'
	  + '<tr>'
+ '<th>Country</th>'
	+ '<td><input type="text" name="add-country" value="'+country+'"/></td>'
	  + '</tr>'
	  + '</table>'
	);
}

$(function(){
	showAvatar();
	$("#tabs").tabs();	
	$('#buy-credits').click(function(){
		$.get('/ww.plugins/privacy/get-credit-details.php',function(ret){
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
					'<form id="online-store-paypal" method="post" action="https://www.sandbox.paypal.com/cgi-bin/webscr">'
					+'<input type="hidden" value="_xclick" name="cmd"/>'
					+'<input type="hidden" value="k_ounu_1352645404_biz@yahoo.com" name="business"/>'
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
						/home.*/,'ww.plugins/privacy/verify-paypal.php'
					)
					+'" name="notify_url"/>'
					+'<input type="hidden" value="IC_Sample" name="bn"/>'
                                        +'<input type="hidden" name="custom" value="'+$('#avatar-wrapper').data('uid').toString()+'"/>'
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

$('body').on('click', '.delete-addr', function(){
	var name=$(this).attr('name');
	$(this).parent().parent().fadeOut('slow').remove();
	$.get('/ww.plugins/privacy/frontend/save_user_info.php?action=delete'
		+'&address='+name
	);
});
