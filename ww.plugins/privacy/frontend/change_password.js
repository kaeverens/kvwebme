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
	$( "<div id='password-dialog' title='Change User Password'></div>" )
		.html( "Loading..." )
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
		var html = '<p id="password-error"></p>'
		+ '<table id="password-table">'
			+ '<tr id="password-current">'
				+ '<th>Current Password:</th>'
				+ '<td><input type="password" name="password-current"/></td>'
			+ '</tr>'
			+ '<tr style="display:none" class="passwords">'
				+ '<th>New Password:</th>'
				+ '<td><input type="password" name="password-new"/></td>'
			+ '</tr>'
			+ '<tr style="display:none" class="passwords">'
				+ '<th>Repeat Password:</th>'
				+ '<td><input type="password" name="password-repeat"/></td>'
			+ '</tr>'
		+ '</table>';

		$( '#password-dialog' ).html( html );
}

$( 'input[name="password-current"]' ).live( 'keyup', function( ){
	var pass = $( this ).val( );
	$.post(
		'/ww.plugins/privacy/frontend/check_password.php',
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
	$.post(
		'/ww.plugins/privacy/frontend/save_password.php',
		{ "pass" : pass, "match" : match }
	);
	$( '#password-dialog' ).dialog( 'close' ).remove( );
}
