/**
 * frontend/change_password.js, KV-Webme Privacy Plugin
 *
 * allows the user to change their password
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

function change_password_dialog( id ){
	var html ='<div id="password-dialog"><p id="password-error"></p>'
		+ '<table id="password-table">'
			+ '<tr id="password-current">'
				+ '<th class="__" lang-context="core">Current Password:</th>'
				+ '<td><input type="password" name="password-current"/></td>'
			+ '</tr>'
			+ '<tr style="display:none" class="passwords">'
				+ '<th class="__" lang-context="core">New Password:</th>'
				+ '<td><input type="password" name="password-new"/></td>'
			+ '</tr>'
			+ '<tr style="display:none" class="passwords">'
				+ '<th class="__" lang-context="core">Repeat Password:</th>'
				+ '<td><input type="password" name="password-repeat"/></td>'
			+ '</tr>'
		+ '</table></div>';
	$( html )
		.dialog({
			modal : true,
			buttons : {
				"Save" : function( ){
					validate_passwords( );
				},
				"Cancel" : function( ){
					$( "#password-dialog" ).dialog( "close" ).remove( );
				}
 			}
 		});
	$('#password-dialog').closest('.ui-dialog').find('.ui-dialog-buttonset button .ui-button-text')
		.attr('lang-context', 'core')
		.addClass('__');
	__langInit();
}

$('input[name="password-current"]').live('keyup', function(){
	var pass = $( this ).val( );
	$.post('/ww.plugins/privacy/frontend/check_password.php',
		{ 'pass' : pass },
		function( html ){
			if( html == 'correct' ){
				$( '#password-error' ).html( 'Current Password Correct' );
				$( '#password-current' ).remove( );
				$( '.passwords' ).css({ 'display' : 'block' });
			} 
			else{
				$( '#password-error' ).html( 'Current Password Incorrect' );
			}
		}
	);
} );

function validate_passwords( ){
	var pass = $( 'input[name="password-new"]' ).val( );
	var match = $( 'input[name="password-repeat"]' ).val( );
	if( pass != match ){
		$( '#password-error' ).html( 'Passwords do not match' );
		return false;
	}
	$.post('/ww.plugins/privacy/frontend/save_password.php',
		{ "pass" : pass, "match" : match }
	);
	$( '#password-dialog' ).dialog( 'close' ).remove( );
}

$(function(){
	$.get('/a/f=getUserData',
		function(user){
			$.extend(userdata, user);
		},
		'json'
	);
	$('input[name="default-address"]').live('click',function(){
		var name=$(this).val();
		userdata.address[name].default='yes';
		$.get('/ww.plugins/privacy/frontend/save_user_info.php?action=default&name='
			+ name
		);
	});
});

function add_address(){
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
            $.post("/ww.plugins/privacy/frontend/save_user_info.php?action=update",
              {
                "name" : safe,
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
					$('#address-container table tr:last').after(
      '<tr>'
       +'<td>'
         +'<input type="radio" name="default-address" value="'+safe+'"/>'
        +'</td>'
        +'<td>'+name+'</td>'
        +'<td>'
          +'<a href="javascript:edit_address(\''+safe+'\');" class="edit-addr" name="'+safe+'">[edit]</a>'
          +'<a href="javascript:;" class="delete-addr" name="'+safe+'">[delete]</a>'
        +'</td>'
     +' </tr>'
					);
					$(this).dialog('close').remove();
				},
				'Cancel':function(){
					$(this).dialog('close').remove();
				},
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
};
