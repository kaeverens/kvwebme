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
	$("#tabs").tabs();
});
$(".delete-addr").live("click",function(){
	var name=$(this).attr("name");
	$(this).parent().parent().fadeOut("slow").remove();
	$.get("/ww.plugins/privacy/frontend/save_user_info.php?action=delete"
		+"&address="+name
	);
});
