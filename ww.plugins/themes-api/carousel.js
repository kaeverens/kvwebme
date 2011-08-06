/**
 * api.js, KV-Webme Themes Repository
 *
 * file for accessing apects of the repository via js
 */
var Carousel = {
	themes : {},
	position : 0,
	tempFade : false,
	selector : null,
	// holds the current number of items being displayed
	// - always less than settings.items
	current : 0,
	// how the themes are filtered,
	// should be made into an option at
	// some stage
	filter : 'recent',
	settings : {
		items	: 6,
		rows	: 2,
		width : '860px',
		nextLinkText: 'Next',
		prevLinkText: 'Previous',
		repeat: true,
		fade : false,
		loop : false,
		// error message to be displayed if server cannot
		// be reached
		error : 'There was an error contacting themes server.'
			+ 'Please try reloading the page',
		// shows a select box of methods
		// of filtering the themes by categories
		filterOptions : true,
		// custom display function
		// must increment this.current and
		// this.position
		display : null,
		// callback function to be executed
		// each time a new set of themes is loaded
		callback : null
	},
	themesLength : function( ){
		var size = 0, key;

		for ( key in this.themes ) {
			++size;
		}

		return size;
	},
	mergeThemes : function( new_themes ){
		var length = this.themesLength( );

		for( var key in new_themes ){ 
			this.themes[ length ] = new_themes[ key ];
			++length;
		} 
	},
	search : function( value ){
		alert( value );
	},
	filterBy : function( by ){ // changes how themes are filtered
		this.themes = {};
		this.position = 0;
		this.filter = by;
		this.tempFade = true;
		$( '.slider .themes-container', this.selector ).html(
			'Loading...'
		);
		this.displayNext( );
	},
	getThemes : function( ){
		$.ajax({
			url : '/ww.incs/proxy.php?url=http://kvweb.me/ww.plugins/themes-api/'
//				url : '/ww.plugins/themes-api/'
			+ 'api.php?' + this.filter + '=true&count=' + this.settings.items
			+ '&start=' + this.themesLength( ),
			success : function( complete ){
				Carousel.mergeThemes( complete );
				Carousel.displayNext( );
			},
			error : function( error ){
				if( error.responseText == 'none' && Carousel.settings.loop == true ){
					Carousel.position = 0;
					Carousel.displayNext( );
				}
				else{
				
					$( '.slider .themes-container', this.selector ).html( Carousel.settings.error );
				}
			},
			dataType : 'json'
		});
	}, 	 
	displayThemes : function( ){ 

		// this should be incremented in a custom display function
		this.current = 0;

		// allow for custom display function
		if( typeof( this.settings.display ) == 'function' ) {
			return this.settings.display( );
		}

		var html = '<div class="themes-container"><table><tr>';

		for( var i = 0; i < this.settings.items; ++i ){

			if( this.themes[ this.position ] == undefined ) {
				break;
			}

			if( i % ( this.settings.items / this.settings.rows ) === 0 ) {
				html += '</tr><tr>';
			}

			var middle = ( !( ( i - 1 ) % ( this.settings.items / this.settings.rows ) ) ) ?
				' middle' :
				'';

			html += '<td style="width:240px" class="carousel-theme-container' + middle + '">'
			+ '<h2><a href="http://kvweb.me">' + this.themes[ this.position ].name + '</a></h2>'
			+ '<a href="http://kvweb.me/"><img src="' + this.themes[ this.position ].screenshot + '" style="width:240px;height:172px"/>'
			+ '<p>' + this.themes[ this.position ].short_description + '</p>'
			+ '<p><a href="http://kvweb.me/">Read More..</a></p>'
			+ '</td>';

			++this.position;
			++this.current;

		}   

		html += '</tr></table></div>';

		return html;
	},
	displayNext : function( ){
		if( this.position >= this.themesLength( ) ){
			this.getThemes( );
		}
		else{  
			if( this.settings.fade == true || this.tempFade == true ){
				this.fadeNext( );
				this.tempFade = false;
				if( typeof( this.settings.callback ) == 'function' ) {
					return this.settings.callback( );
				}
			}
			else {
				this.slideNext( );
			}
		}
	}, 
	fadeNext : function( ){
		var html = this.displayThemes( );
		$( '.slider', this.selector )
			.css({ 'display' : 'none' })
			.html( html )
			.fadeIn( 'slow',function( ){
				if (typeof( Carousel.settings.callback ) == 'function' ) {
					return Carousel.settings.callback( );
				}
			});
	}, 
	slideNext : function( ){
		var html = this.displayThemes( );
		$( '.themes-container', this.selector ).css({ 'left' : '-850px' });
		$( '.slider', this.selector )
			.append( $( html ).css({ 'left' : '0' }) )
			.css({ 'left' : '850px' } ) 
			.animate({ 'left' : '0' }, 1750, function( ){
				$( '.slider .themes-container:first', this.selector ).remove( );
				if (typeof( Carousel.settings.callback ) == 'function' ) {
					return Carousel.settings.callback( );
				}
			});
	}, 
	displayPrevious : function( ){
		// { if position = 0
		if (this.position == this.settings.items ) {
			if (this.settings.loop == true ) {
				var remainder = this.themesLength( ) % this.current;
				if( remainder == 0 ) {
					this.position = this.themesLength( ) - this.current;
				}
				else {
					this.position = this.themesLength( ) - remainder;
				}
			}
			else {
				return;
			}
		}
		else {
			this.position -= ( this.current + this.settings.items );
		}
		// }

		if( this.settings.fade == true ) {
			this.fadeNext( );
		}
		else {
			this.slidePrevious( );
		}
	},
	slidePrevious : function( ){
		var html = this.displayThemes( );
		$( '.themes-container', this.selector ).css({ 'left' : '850px' }); 
		$( '.slider', this.selector )
			.prepend( $( html ).css({ 'left' : '0' }) )
			.css({ 'left' : '-850px' } ) 
			.animate({ 'left' : '0' }, 1750, function( ){   
				$( '.slider .themes-container:last', this.selector ).remove( );
				if( typeof( Carousel.settings.callback ) == 'function' ) {
					return Carousel.settings.callback( );
				}
			}); 
	},
	init : function( ){
		// { add some css rules
		var height = ( 340 * this.settings.rows ) + 'px';
		$( this.selector ).css({ 'overflow-y' : 'hidden', 'overflow-x' : 'visible', 'width' : this.settings.width }); 

		$( this.selector ).html(
			'<div class="themes-carousel-wrapper">'
			+ '<div class="themes-carousel-options"></div>'
			+ '<div style="position:relative;min-height:'
			+ height + '" class="slider">'
				+ '<br style="clear:both"/>'
			+ '</div></div></div>'
		);

		if( this.settings.filterOptions ){
			var filter = '<span style="float:right">'
			+ '<a class="previous" href="javascript:;">'+this.settings.prevLinkText+'</a> | '
			+ '<a class="next" href="javascript:;">'+this.settings.nextLinkText+'</a> '
			+ '<select name="themes_filter">'
				+ '<option value="recent" selected="selected">Recently Added</option>'
				+ '<option value="downloads">Most Downloads</option>'
				+ '<option value="rating">Highest Rated</option>'
			+ '</select>'
			+ '</span><br style="clear:both"/>';

			$( '.themes-carousel-options' ).html( filter );

			$( 'select[name="themes_filter"]' ).change(function( ){
				var value = $( this ).val( );
				Carousel.filterBy( value );
			});
		}
		// }

		$( '.previous' ).live( 'click', function( ){
			Carousel.displayPrevious( );
		});
		$( '.next' ).live( 'click', function( ){
			Carousel.displayNext( );
		});

		// fade in the first time
		this.tempFade = true;
		this.displayNext( );

	} 
};

/**
 * add .themesCarousel
 */
(function( $ ){
	$.fn.themesCarousel = function( options, value ){

		if( typeof( options ) == 'string' ){
			switch( options ){
				case 'next':
					Carousel.displayNext( );
				break;
				case 'previous':
					Carousel.displayPrevious( );
				break;
				case 'search':
					if( value != null ) {
						Carousel.search( value );
					}
				break;
			}
		}
		else{
			$.extend( Carousel.settings, options );
			Carousel.selector = this.selector;
			Carousel.init( );
		}

		return this;
	}
})( jQuery );
