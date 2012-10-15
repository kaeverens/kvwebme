function themes_dialog(html) {
	var $dialog=$('#themes-dialog');
	if (!$dialog.length) {
		$dialog=$('<div id="themes-dialog"/>').dialog({
			'modal':'true',
			close: function() {
				$(this).remove();
			}
		});
	}
	$dialog.append(html);
}
$(function(){
	$( '#tabs' ).tabs( );
	$( '.theme_variant' ).each( show_screenshot );
	$( '.theme_variant' ).change( show_screenshot );
	function show_screenshot( ){
		var screenshot = $( ':selected', this ).attr( 'screenshot' );
		$( this ).closest( 'div' ).find( 'img' ).attr( 'src', screenshot );
	}
	$( '.theme-preview-personal' ).click( function( ){
		$( '#preview-frame' ).attr( 'src', '' );
		var name = $( this ).attr( 'title' );
		var variant = $( this ).closest( 'form' ).find( '.theme_variant' ).val( );
		$( '#preview-dialog' ).attr( 'title', name + ' - Theme Preview'  );
		$( '#preview-frame' ).attr( 'src', '/?__theme=' + name + '&__theme_variant=' + variant );
		$( '#preview-frame' ).attr( 'height', $( window ).height( ) - 140 );
		$( '#preview-frame' ).attr( 'width', $( window ).width( ) -220 );
		$( '#preview-dialog' ).dialog( {
			modal: true,
			width: $( window ).width( ) - 200,
			height: $( window ).height( ) - 60
		} );
	});
	$('#themes-upload-form').live('submit', function() {
		themes_dialog('<p>uploading. please wait.</p>');
	});
});
