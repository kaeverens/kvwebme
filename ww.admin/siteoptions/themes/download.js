/*global Carousel*/
$( function( ){

	$( '.theme_variant' ).live({
		each : show_screenshot,
		change : show_screenshot
	});

	function show_screenshot( ){
			var screenshot = $( ':selected', this ).attr( 'screenshot' );
			$( this ).closest( 'div' ).find( '.screenshot' ).attr( 'src', screenshot );
	}

	$( '.install-theme' ).live( 'click', function( ){
		/**
		 * check if theme is already installed
		 */
		var installed = $( this ).attr( 'installed' );
		if( installed == 1 ){
			var conf = confirm( 'You already have a local copy of Carousel.theme. Proceeding will erase the theme from your local themes and replace it. Do you wish to proceed?' );
			if( conf === false ) {
				return false;
			}
		}
	});

	$( '.theme-preview-download' ).live( 'click', function( ){
		$( '#preview-frame' ).attr( 'src', '' );
		var name = $( this ).attr( 'title' );
		var variant = $( this ).closest( 'form' ).find( '.theme_variant' ).val( );
		$( '#preview-dialog' ).attr( 'title', name + ' - Theme Preview'	);
		$( '#preview-frame' ).attr( 'src', 'http://kvweb.me/preview?__theme=' + name + '&__theme_variant=' + variant );
		$( '#preview-frame' ).attr( 'height', $( window ).height( ) - 140 );
		$( '#preview-frame' ).attr( 'width', $( window ).width( ) -220 );
		$( '#preview-dialog' ).dialog( { modal: true, width: $( window ).width( ) - 200, height: $( window ).height( ) - 60 } );
	});

	$( '#themes-carousel' ).themesCarousel({ 'display' : function( ){


			var html = '<div class="themes-container"><table><tr>';

			for( var i = 0; i < Carousel.settings.items; ++i ){

				if( Carousel.themes[ Carousel.position ] === undefined ) {
					break;
				}

				if (i % ( Carousel.settings.items / Carousel.settings.rows ) === 0 ) {
					html += '</tr><tr>';
				}

				var installed = ( $.inArray( Carousel.themes[ Carousel.position ].name,
												window.installed_themes ) != -1 ) ? 1 : 0;

				var stat = ( installed ) ? ' - Already Installed' : '';

				var middle = ( !( ( i - 1 ) % ( Carousel.settings.items / Carousel.settings.rows ) ) ) ?
					' middle' :
					'';

				html += '<td class="carousel-theme-container' + middle + '">'+
					'<form action="/ww.admin/siteoptions.php?page=themes&action=download" method="post">'+
					'<input type="hidden" value="' + Carousel.themes[ Carousel.position ].id+
					'" name="theme_id"/>'+
					'<h3>' + Carousel.themes[ Carousel.position ].name + stat + '</h3>'+
					'<p><img src="http://kvweb.me/ww.plugins/themes-api/api.php?'+
					'screenshot=true&id=' + Carousel.themes[ Carousel.position ].id+
					'"/></p>'+
					'<p class="ratings" id="themes_' + Carousel.themes[ Carousel.position ].id+
					'" type="theme">ratings</p>'+
					'<p>' + Carousel.themes[ Carousel.position ].description + '</p>';

				// print variants, if present
				if( Carousel.themes[ Carousel.position ].variants.length ){
					html += '<p>Variant: <select name="theme_variant" class="theme_variant">';
					for( var e in Carousel.themes[ Carousel.position ].variants ){
						html += '<option screenshot="' + Carousel.themes[ Carousel.position ].screenshot+
							'&variant=' + Carousel.themes[ Carousel.position ].variants[ e ]+
							'">' + Carousel.themes[ Carousel.position ].variants[ e ] + '</option>';
					}
					html += '</select></p>';
				}

				html += '<p><i>Total Downloads: ' + Carousel.themes[ Carousel.position ].downloads+
					'</i></p>'+
					'<input type="submit" class="install-theme" name="install-theme"'+
					' installed="' + installed + '" value="Download & Install" />'+
					'<input type="submit" class="install-theme" name="download-theme"'+
					' installed="' + installed + '" value="Download"/>'+
					' <a class="theme-preview theme-preview-download" '+
					'title="' +Carousel.themes[ Carousel.position ].name + '" >Preview</a>'+
					'</form></td>';

				++Carousel.position;
				++Carousel.current;
			}

			return html;

	},
	callback : function( ){
		$( '.ratings', Carousel.selector ).ratings({ saveRemotely : '/ww.incs/proxy.php?url=http://kvweb.me' } );
	},
	loop : true
	});
});
