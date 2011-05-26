/**
 * api.js, KV-Webme Themes Repository
 *
 * file for accessing apects of the repository via js

plan

load 3 themes,	=> function
display them,	=> function
when next is clicked	=> bind
check if themes are already downloaded => function
[if not]
	load 3 themes,	=> function
display them,	=> function

when previous is clicked => bind
check if themes are already downloaded => function
[if not]
        load 3 themes,  => function
display them,   => function

 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * load jquery carousel
 */
$.getScript( '/ww.plugins/themes-api/files/jcarousel.jquery.min.js' );

/**
 * list_themes
 *
 * lists the themes in a <ul>
 */
function list_themes( themes ){

	var html = '<div class="themes-list-container">'
		+ '<a class="themes-prev"><</a>'
		+ '<ul class="themes-list">';

	for( var i = 0; i < themes.length; ++i ){

		html +='<li>'
			+ '<div class="theme-container">'
				+ '<h2><a href="http://kvweb.me">' + themes[ i ].name + '</a></h2>'
				+ '<a href="http://kvweb.me/"><img src="' + themes[ i ].screenshot + '" width="240px" height="172px"/>'
				+ '<p>' + themes[ i ].short_description + '</p>'
				+ '<p><a href="http://kvweb.me/">Read More..</a></p>'
			+ '</div>'
			+  '</li>';
	}

	html += '</ul>'
		+ '<a class="themes-next">></a>'
		+ '</div>';

	return html;

}

/**
 * add .themesCarousel
 */
(function( $ ){

	var themes = { };
	var position = 0;
	var selector;
	var settings = {
                        'display'       :       'recent',
                        'items'         :       3,
                        'scroll'        :       1
	};

	var getThemesLength = function( ){

		var size = 0, key;

		for ( key in themes )
			++size;

		return size;

	};

	var mergeThemes = function( result ){

		var length = getThemesLength( );

		for( var key in result ){
			themes[ length ] = result[ key ];
			++length;
		}		

	};

	var getThemes = function( complete ){

		var rand = Math.floor( Math.random( ) * 101 );
		var length = getThemesLength( );

                $.ajax( {
			url : '/ww.incs/proxy.php?url=http://kvweb.me/ww.plugins/themes-api/api.php?recent=true&count=' + settings.items + '&start=' + length + '&rand=' + rand,
//			url : '/ww.incs/proxy.php?url=http://webme.l/ww.plugins/themes-api/api.php?recent=true&count=' + settings.items + '&start=' + length + '&rand=' + rand,
			success : function( result ){

				mergeThemes( result );

				if( )
					$( '.slider' ).html( displayThemes( 0 ) );
				else
					displayNext( );

				//$( '.slider', selector ).html( displayThemes( 0 ) );
				
                	},
			error : function( ){
				position -= settings.items;
			},
			dataType : 'json' 
		});

	};

	var displayThemes = function( start ){

		var html = '<div class="themes-container">';

		for( var i = 0; i < settings.items; ++i ){

			if( !themes[ start ] )
				break;

	                html += '<div class="theme-container">'
                	                + '<h2><a href="http://kvweb.me">' + themes[ start ].name + '</a></h2>'
                        	        + '<a href="http://kvweb.me/"><img src="' + themes[ start ].screenshot + '" width="240px" height="172px"/>'
                                	+ '<p>' + themes[ start ].short_description + '</p>'
	                                + '<p><a href="http://kvweb.me/">Read More..</a></p>'
        	                + '</div>';

			++start;

		}

		html += '</div>';

		return html;
	};

	var displayNext = function( ){

		position += settings.items;

		alert( position + " >= " + settings.items + "=" + ( position >= getThemesLength( ) ) );

		if( position >= getThemesLength( ) )
			getThemes( );
		else{
			var html = displayThemes( position );
	                $( '.themes-container', selector ).css({ 'left' : '-850px' });
        	        $( '.slider', selector )
                	        .prepend( $( html ).css({ 'left' : '0' }) )
                        	.css({ 'left' : '850px' } )
	                        .animate({ 'left' : '0' }, 2000, function( ){
        	                        $( '.slider .themes-container:last', selector ).remove( );
                	        });

		}
	};

	var displayPrevious = function( ){

		if( position == 0 )
			return;

		position -= settings.items;

		var html = displayThemes( position );
		$( '.themes-container', selector ).css({ 'left' : '850px' });
		$( '.slider', selector )
			.prepend( $( html ).css({ 'left' : '0' }) )
			.css({ 'left' : '-850px' } )
			.animate({ 'left' : '0' }, 2000, function( ){		
				$( '.slider .themes-container:last', selector ).remove( );
			});

	};

	$.fn.themesCarousel = function( options ){

		/**
		 * allow for next and prev events
		 */
		if( typeof( options ) == 'string' ){
			switch( options ){
				case 'next':
					displayNext( );
				break;
				case 'prev':
					displayPrevious( );
				break;
			}
			return this;
		}

		if( options )
			$.extend( settings, options );

		selector = this.selector;

		$( selector ).css({ 'overflow' : 'hidden', 'width' : '850px' });
		$( selector ).html( '<div style="position:relative;height:340px" class="slider"></div>' );

		getThemes( );

		return this;

	}

})( jQuery );

